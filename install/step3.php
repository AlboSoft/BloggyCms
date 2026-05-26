<?php
session_start();

$current_lang = $_SESSION['install_lang'] ?? 'ru';

$lang = [
    'ru' => [
        'title' => 'Администратор',
        'subtitle' => 'Создайте учетную запись администратора',
        'blog_settings' => 'Настройки блога',
        'site_name' => 'Название блога',
        'site_name_hint' => 'Как будет называться ваш блог',
        'site_url' => 'URL блога',
        'site_url_hint' => 'Полный адрес без слеша в конце',
        'admin_data' => 'Данные администратора',
        'username' => 'Имя пользователя',
        'username_hint' => 'Минимум 3 символа',
        'email' => 'Email',
        'email_hint' => 'Для восстановления пароля',
        'password' => 'Пароль',
        'password_confirm' => 'Подтверждение',
        'password_confirm_hint' => 'Введите пароль еще раз',
        'password_weak' => 'Слабый пароль',
        'password_medium' => 'Средний пароль',
        'password_strong' => 'Надёжный пароль',
        'generate' => 'Сгенерировать',
        'important' => 'Важно',
        'save_credentials' => 'Сохраните данные администратора в надёжном месте',
        'back' => 'Назад',
        'install' => 'Установить',
        'error' => 'Ошибка',
        'language_settings' => 'Языковые настройки',
        'admin_language' => 'Язык панели управления',
        'site_language' => 'Язык сайта',
        'admin_language_hint' => 'Язык интерфейса административной панели',
        'site_language_hint' => 'Основной язык контента сайта'
    ],
    'en' => [
        'title' => 'Administrator',
        'subtitle' => 'Create an administrator account',
        'blog_settings' => 'Blog Settings',
        'site_name' => 'Site Name',
        'site_name_hint' => 'What will your blog be called',
        'site_url' => 'Site URL',
        'site_url_hint' => 'Full address without trailing slash',
        'admin_data' => 'Administrator Data',
        'username' => 'Username',
        'username_hint' => 'Minimum 3 characters',
        'email' => 'Email',
        'email_hint' => 'For password recovery',
        'password' => 'Password',
        'password_confirm' => 'Confirm Password',
        'password_confirm_hint' => 'Enter the password again',
        'password_weak' => 'Weak password',
        'password_medium' => 'Medium password',
        'password_strong' => 'Strong password',
        'generate' => 'Generate',
        'important' => 'Important',
        'save_credentials' => 'Save your administrator credentials in a safe place',
        'back' => 'Back',
        'install' => 'Install',
        'error' => 'Error',
        'language_settings' => 'Language Settings',
        'admin_language' => 'Admin Panel Language',
        'site_language' => 'Site Language',
        'admin_language_hint' => 'Administrative panel interface language',
        'site_language_hint' => 'Main site content language'
    ]
];

$t = $lang[$current_lang];

$availableLocales = [];
$languagesPath = __DIR__ . '/../system/languages';

if (is_dir($languagesPath)) {
    $dirs = scandir($languagesPath);
    foreach ($dirs as $dir) {
        if ($dir === '.' || $dir === '..') continue;
        if (!is_dir($languagesPath . '/' . $dir)) continue;
        
        $langFile = $languagesPath . '/' . $dir . '/core/Language.php';
        $langName = $dir;
        
        if (file_exists($langFile)) {
            $langData = include $langFile;
            if (is_array($langData)) {
                if (isset($langData['native_name'])) {
                    $langName = $langData['native_name'];
                } elseif (isset($langData['name'])) {
                    $langName = $langData['name'];
                }
            }
        }
        
        $availableLocales[$dir] = $langName;
    }
}

if (empty($availableLocales)) {
    $availableLocales['ru_RU'] = 'Русский';
    $availableLocales['en_En'] = 'English';
}

if (!isset($_SESSION['db_connected']) || !$_SESSION['db_connected']) {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
}

function importDemoSql(PDO $pdo, string $prefix): void {
    $demoFile = __DIR__ . '/demo.sql';
    if (!file_exists($demoFile)) {
        throw new Exception("Файл demo.sql не найден");
    }
    
    $sql = file_get_contents($demoFile);
    $sql = str_replace('{#}', $prefix, $sql);
    $sql = preg_replace('/^--.*$/m', '', $sql);
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
    
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            try {
                $pdo->exec($query);
            } catch (PDOException $e) {
                $msg = $e->getMessage();
                if (strpos($msg, 'Duplicate') === false && strpos($msg, 'already exists') === false) {
                    throw new Exception("Demo SQL Error: " . $msg);
                }
            }
        }
    }
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
}

function updateSiteSettings(PDO $pdo, string $prefix, array $siteConfig): void {
    $generalSettings = [
        'site_name' => $siteConfig['site_name'],
        'date_format' => 'd.m.Y',
        'time_format' => 'H:i',
        'site_author' => $siteConfig['admin_username'],
        'contact_email' => $siteConfig['admin_email'],
        'site_tagline' => 'Мой блог',
        'site_description' => 'Современный блог на BloggyCMS',
        'meta_keywords' => 'блог, cms, php, программирование',
        'enable_sitemap' => '1',
        'enable_robots_txt' => '1',
        'maintenance_message' => 'Сайт временно недоступен. Ведутся технические работы.',
        'admin_language' => $siteConfig['admin_language'],
        'site_language' => $siteConfig['site_language'],
        'allow_user_language_switch' => 1
    ];
    $generalJson = json_encode($generalSettings, JSON_UNESCAPED_UNICODE);
    
    $stmt = $pdo->prepare("SELECT id FROM `{$prefix}settings` WHERE group_key = 'general'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $sql = "UPDATE `{$prefix}settings` SET settings = :settings WHERE group_key = 'general'";
    } else {
        $sql = "INSERT INTO `{$prefix}settings` (group_key, settings) VALUES ('general', :settings)";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':settings' => $generalJson]);
    
    $siteSettings = [
        'base_url' => rtrim($siteConfig['site_url'], '/'),
        'site_template' => 'default',
        'template_backups_enabled' => '0',
        'template_backups_count' => '5',
        'template_backups_cleanup' => 'auto'
    ];
    $siteJson = json_encode($siteSettings, JSON_UNESCAPED_UNICODE);
    
    $stmt = $pdo->prepare("SELECT id FROM `{$prefix}settings` WHERE group_key = 'site'");
    $stmt->execute();
    if ($stmt->fetch()) {
        $sql = "UPDATE `{$prefix}settings` SET settings = :settings WHERE group_key = 'site'";
    } else {
        $sql = "INSERT INTO `{$prefix}settings` (group_key, settings) VALUES ('site', :settings)";
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':settings' => $siteJson]);
    
    try {
        $stmt = $pdo->prepare("SELECT id, settings FROM `{$prefix}html_blocks` WHERE slug = 'header' LIMIT 1");
        $stmt->execute();
        $header = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($header && !empty($header['settings'])) {
            $settings = json_decode($header['settings'], true);
            if (is_array($settings)) {
                $settings['site_name'] = $siteConfig['site_name'];
                $newSettings = json_encode($settings, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("UPDATE `{$prefix}html_blocks` SET settings = :settings WHERE id = :id");
                $stmt->execute([':settings' => $newSettings, ':id' => $header['id']]);
            }
        }
        
        $stmt = $pdo->prepare("SELECT id, settings FROM `{$prefix}html_blocks` WHERE slug = 'footer' LIMIT 1");
        $stmt->execute();
        $footer = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($footer && !empty($footer['settings'])) {
            $settings = json_decode($footer['settings'], true);
            if (is_array($settings)) {
                $settings['site_name'] = $siteConfig['site_name'];
                $newSettings = json_encode($settings, JSON_UNESCAPED_UNICODE);
                $stmt = $pdo->prepare("UPDATE `{$prefix}html_blocks` SET settings = :settings WHERE id = :id");
                $stmt->execute([':settings' => $newSettings, ':id' => $footer['id']]);
            }
        }
    } catch (PDOException $e) {
        error_log('HTML blocks update failed: ' . $e->getMessage());
    }
}

$errors = [];
$siteConfig = [
    'site_name' => 'Мой блог', 
    'site_url' => '', 
    'admin_email' => '', 
    'admin_username' => 'admin', 
    'admin_password' => '', 
    'admin_password_confirm' => '',
    'admin_language' => 'ru_RU',
    'site_language' => 'ru_RU'
];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$siteConfig['site_url'] = $protocol . $_SERVER['HTTP_HOST'] . rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');
if (isset($_SESSION['site_config'])) $siteConfig = array_merge($siteConfig, $_SESSION['site_config']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $siteConfig = array_merge($siteConfig, [
        'site_name' => trim($_POST['site_name']),
        'site_url' => rtrim($_POST['site_url'], '/'),
        'admin_email' => trim($_POST['admin_email']),
        'admin_username' => trim($_POST['admin_username']),
        'admin_password' => $_POST['admin_password'],
        'admin_password_confirm' => $_POST['admin_password_confirm'],
        'admin_language' => $_POST['admin_language'] ?? 'ru_RU',
        'site_language' => $_POST['site_language'] ?? 'ru_RU'
    ]);
    $_SESSION['site_config'] = $siteConfig;

    if (empty($siteConfig['site_name'])) $errors[] = $t['site_name'] . ' - ' . ($current_lang == 'ru' ? 'обязательное поле' : 'required field');
    if (empty($siteConfig['admin_email']) || !filter_var($siteConfig['admin_email'], FILTER_VALIDATE_EMAIL)) $errors[] = $t['email'] . ' - ' . ($current_lang == 'ru' ? 'некорректный email' : 'invalid email');
    if (empty($siteConfig['admin_username']) || strlen($siteConfig['admin_username']) < 3) $errors[] = $t['username'] . ' - ' . ($current_lang == 'ru' ? 'минимум 3 символа' : 'minimum 3 characters');
    if (empty($siteConfig['admin_password'])) $errors[] = $t['password'] . ' - ' . ($current_lang == 'ru' ? 'обязательное поле' : 'required field');
    elseif (strlen($siteConfig['admin_password']) < 6) $errors[] = $t['password'] . ' - ' . ($current_lang == 'ru' ? 'минимум 6 символов' : 'minimum 6 characters');
    elseif ($siteConfig['admin_password'] !== $siteConfig['admin_password_confirm']) $errors[] = $t['password'] . ' - ' . ($current_lang == 'ru' ? 'пароли не совпадают' : 'passwords do not match');

    if (empty($errors)) {
        try {
            $db = $_SESSION['db_config'];
            $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset=utf8mb4";
            $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 30]);
            
            $sql = file_get_contents('install.sql');
            $sql = preg_replace('/^--.*$/m', '', $sql);
            $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
            $prefix = $db['prefix'];
            $sql = str_replace('{#}', $prefix, $sql);
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
            $critical = [];
            foreach ($queries as $query) {
                if (!empty($query)) {
                    try { $pdo->exec($query); } catch (PDOException $e) {
                        $msg = $e->getMessage();
                        if (strpos($msg,'already exists')===false && strpos($msg,'Duplicate')===false) {
                            $critical[] = "SQL Error: " . $msg;
                        }
                    }
                }
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
            if (!empty($critical)) throw new Exception(implode("<br>", $critical));

            if (!empty($db['install_demo'])) {
                importDemoSql($pdo, $db['prefix']);
            }
            
            updateSiteSettings($pdo, $db['prefix'], $siteConfig);

            $hashedPassword = password_hash($siteConfig['admin_password'], PASSWORD_DEFAULT);
            $usersTable = $prefix . 'users';
            $pdo->query("SELECT 1 FROM `{$usersTable}` LIMIT 1");
            $stmt = $pdo->prepare("SELECT id FROM `{$usersTable}` WHERE username = ? OR email = ?");
            $stmt->execute([$siteConfig['admin_username'], $siteConfig['admin_email']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $stmt = $pdo->prepare("UPDATE `{$usersTable}` SET password = ?, email = ?, is_admin = 1 WHERE id = ?");
                $stmt->execute([$hashedPassword, $siteConfig['admin_email'], $existing['id']]);
                $userId = $existing['id'];
            } else {
                $stmt = $pdo->prepare("INSERT INTO `{$usersTable}` (username, password, email, is_admin, status, created_at) VALUES (?, ?, ?, 1, 'active', NOW())");
                $stmt->execute([$siteConfig['admin_username'], $hashedPassword, $siteConfig['admin_email']]);
                $userId = $pdo->lastInsertId();
            }

            $configDir = '../system/config';
            if (!file_exists($configDir)) mkdir($configDir, 0755, true);
            if (!is_writable($configDir)) throw new Exception("Директория не доступна для записи");

            $dbContent = "<?php
            define('DB_HOST', '".addslashes($db['host'])."');
            define('DB_NAME', '".addslashes($db['name'])."');
            define('DB_USER', '".addslashes($db['user'])."');
            define('DB_PASS', '".addslashes($db['pass'])."');
            define('DB_PREFIX', '".addslashes($db['prefix'])."');
            define('DB_CHARSET', 'utf8mb4');
            define('DB_COLLATE', 'utf8mb4_unicode_ci');";
            file_put_contents($configDir.'/database.php', $dbContent);

            $configContent = "<?php
            define('BASE_PATH', dirname(dirname(__DIR__)));
            define('SYSTEM_PATH', BASE_PATH.'/system');
            define('TEMPLATES_PATH', BASE_PATH.'/templates');
            define('UPLOADS_PATH', BASE_PATH.'/uploads');
            define('BASE_URL', '".addslashes(rtrim($siteConfig['site_url'],'/'))."');
            define('ADMIN_URL', BASE_URL.'/admin');
            define('DEFAULT_TEMPLATE', 'default');
            define('USER_ONLINE_INTERVAL', 300);
            define('CACHE_DIR', BASE_PATH.'/cache');
            define('ADDONS_TEMP_DIR', UPLOADS_PATH . '/temp_addon/');
            define('LANGUAGES_PATH', BASE_PATH . '/system/languages');
            if(!is_dir(CACHE_DIR)) @mkdir(CACHE_DIR,0755,true);";
            file_put_contents($configDir.'/config.php', $configContent);
            file_put_contents(dirname(__DIR__).'/system/config/install.lock', date('Y-m-d H:i:s')."\n");

            $_SESSION['install_step'] = 4;
            $_SESSION['install_complete'] = true;
            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            $errors[] = ($current_lang == 'ru' ? 'Ошибка установки' : 'Installation error') . ": " . $e->getMessage();
        }
    }
}
?>

<h2><?php echo icon('bs', 'person-badge', '24', 'var(--accent)', '', 'style="margin-right: 8px;"'); ?> <?php echo $t['title']; ?></h2>
<p class="step-subtitle"><?php echo $t['subtitle']; ?></p>

<?php if (!empty($errors)) { ?>
    <div class="alert alert-error"><?php echo icon('bs', 'exclamation-circle', '20'); ?><div><strong><?php echo $t['error']; ?></strong><ul style="margin-top:8px;margin-left:20px"><?php foreach($errors as $e) { ?><li><?php echo htmlspecialchars($e); ?></li><?php } ?></ul></div></div>
<?php } ?>

<form method="post" class="needs-validation" novalidate>
    <h3><?php echo icon('bs', 'globe2', '16'); ?> <?php echo $t['blog_settings']; ?></h3>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['site_name']; ?> <span class="required">*</span></label>
            <input type="text" name="site_name" class="form-input" value="<?php echo htmlspecialchars($siteConfig['site_name']); ?>" required placeholder="Мой блог">
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['site_name_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['site_url']; ?> <span class="required">*</span></label>
            <input type="url" name="site_url" id="site_url" class="form-input" value="<?php echo htmlspecialchars($siteConfig['site_url']); ?>" required>
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['site_url_hint']; ?></div>
        </div>
    </div>

    <h3 style="margin-top: 30px;"><?php echo icon('bs', 'translate', '16'); ?> <?php echo $t['language_settings']; ?></h3>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['admin_language']; ?></label>
            <select name="admin_language" class="form-select" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface);">
                <?php foreach ($availableLocales as $code => $name) { ?>
                    <option value="<?php echo $code; ?>" <?php echo $siteConfig['admin_language'] == $code ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php } ?>
            </select>
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['admin_language_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['site_language']; ?></label>
            <select name="site_language" class="form-select" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--border); background: var(--surface);">
                <?php foreach ($availableLocales as $code => $name) { ?>
                    <option value="<?php echo $code; ?>" <?php echo $siteConfig['site_language'] == $code ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                <?php } ?>
            </select>
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['site_language_hint']; ?></div>
        </div>
    </div>

    <h3 style="margin-top: 30px;"><?php echo icon('bs', 'person-lock', '16'); ?> <?php echo $t['admin_data']; ?></h3>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['username']; ?> <span class="required">*</span></label>
            <input type="text" name="admin_username" class="form-input" value="<?php echo htmlspecialchars($siteConfig['admin_username']); ?>" required minlength="3">
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['username_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['email']; ?> <span class="required">*</span></label>
            <input type="email" name="admin_email" class="form-input" value="<?php echo htmlspecialchars($siteConfig['admin_email']); ?>" required>
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['email_hint']; ?></div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['password']; ?> <span class="required">*</span></label>
            <div class="password-wrapper">
                <input type="password" name="admin_password" id="admin_password"
                       class="form-input" required minlength="6">
                <button type="button" class="password-toggle" title="<?php echo $t['password']; ?>">
                    <?php echo icon('bs', 'eye', '16'); ?>
                </button>
            </div>
            <div class="password-strength">
                <div class="strength-meter"><div class="strength-bar"></div></div>
                <div class="strength-text"></div>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['password_confirm']; ?> <span class="required">*</span></label>
            <div class="password-wrapper">
                <input type="password" name="admin_password_confirm" id="admin_password_confirm"
                       class="form-input" required>
                <button type="button" class="password-toggle" title="<?php echo $t['password_confirm']; ?>">
                    <?php echo icon('bs', 'eye', '16'); ?>
                </button>
            </div>
            <div class="form-hint"><?php echo $t['password_confirm_hint']; ?></div>
        </div>
    </div>

    <div class="alert alert-info" style="margin-top:24px">
        <?php echo icon('bs', 'shield-check', '16'); ?>
        <div><strong><?php echo $t['important']; ?>:</strong> <?php echo $t['save_credentials']; ?></div>
    </div>

    <div class="mt-4 flex-between">
        <a href="index.php" class="btn btn-outline"><?php echo icon('bs', 'arrow-left', '16'); ?> <?php echo $t['back']; ?></a>
        <div class="flex">
            <button type="button" onclick="generatePassword()" class="btn btn-secondary">
                <?php echo icon('bs', 'shuffle', '16'); ?> <?php echo $t['generate']; ?>
            </button>
            <button type="submit" class="btn btn-primary" id="install-btn">
                <?php echo $t['install']; ?> <?php echo icon('bs', 'check-lg', '16'); ?>
            </button>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.password-toggle').forEach(btn => btn.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const icon = this.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    }));

    const pwd = document.getElementById('admin_password');
    if (pwd) {
        const bar = pwd.closest('.form-group').querySelector('.strength-bar');
        const text = pwd.closest('.form-group').querySelector('.strength-text');
        pwd.addEventListener('input', function() {
            const p = this.value;
            let s = 0;
            if (p.length >= 8) s++;
            if (p.length >= 12) s++;
            if (/[A-Z]/.test(p)) s++;
            if (/[0-9]/.test(p)) s++;
            if (/[^A-Za-z0-9]/.test(p)) s++;
            bar.className = 'strength-bar';
            if (s <= 1) {
                bar.classList.add('weak');
                text.textContent = '<?php echo $t['password_weak']; ?>';
            } else if (s === 2) {
                bar.classList.add('medium');
                text.textContent = '<?php echo $t['password_medium']; ?>';
            } else {
                bar.classList.add('strong');
                text.textContent = '<?php echo $t['password_strong']; ?>';
            }
        });
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        const p1 = document.getElementById('admin_password').value;
        const p2 = document.getElementById('admin_password_confirm').value;
        if (p1 !== p2) {
            e.preventDefault();
            alert('<?php echo $t['password_confirm']; ?> - ' + ('<?php echo $current_lang; ?>' === 'ru' ? 'пароли не совпадают' : 'passwords do not match'));
            return;
        }
        if (p1.length < 6) {
            e.preventDefault();
            alert('<?php echo $t['password']; ?> - ' + ('<?php echo $current_lang; ?>' === 'ru' ? 'минимум 6 символов' : 'minimum 6 characters'));
            return;
        }
        const btn = document.getElementById('install-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> <?php echo $t['install']; ?>...';
    });

    window.generatePassword = function() {
        const c = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        let p = '';
        for(let i = 0; i < 12; i++) p += c.charAt(Math.floor(Math.random() * c.length));
        document.getElementById('admin_password').value = p;
        document.getElementById('admin_password_confirm').value = p;
        if (pwd) pwd.dispatchEvent(new Event('input'));
        alert('🎉 <?php echo $current_lang == 'ru' ? 'Пароль сгенерирован!' : 'Password generated!'; ?>');
    };
</script>