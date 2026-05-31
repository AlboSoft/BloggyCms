<?php
session_start();

$current_lang = $_SESSION['install_lang'] ?? 'ru';

$lang = [
    'ru' => [
        'title' => 'Настройка базы данных',
        'subtitle' => 'Настройте подключение к MySQL',
        'host' => 'Хост',
        'port' => 'Порт',
        'db_name' => 'Имя БД',
        'db_user' => 'Пользователь',
        'db_password' => 'Пароль',
        'db_prefix' => 'Префикс таблиц',
        'host_hint' => 'Обычно localhost',
        'port_hint' => 'Обычно 3306',
        'name_hint' => 'База данных должна быть создана заранее',
        'prefix_hint' => 'Например: bc_',
        'test_btn' => 'Проверить',
        'connect_btn' => 'Подключить',
        'back_btn' => 'Назад',
        'checking' => 'Проверка...',
        'error' => 'Ошибка',
        'important' => 'Важно',
        'permissions_hint' => 'Пользователь должен иметь права на создание таблиц в этой БД (SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP)',
        'db_empty_required' => 'База данных должна быть пустой (не содержать таблиц)',
        'db_not_exists' => 'База данных не найдена',
        'connection_error' => 'Ошибка подключения',
        'install_demo' => 'Установить демо данные',
        'install_demo_desc' => 'Если Вы впервые устанавливаете BloggyCms - рекомендуем установить демо-данные, для понимания работы системы',
        'db_connection_success' => 'Подключение успешно! База данных готова к установке.',
        'toggle_password' => 'Показать/скрыть пароль',
        'connection_success' => 'Подключение успешно'
    ],
    'en' => [
        'title' => 'Database Configuration',
        'subtitle' => 'Configure MySQL connection',
        'host' => 'Host',
        'port' => 'Port',
        'db_name' => 'Database Name',
        'db_user' => 'User',
        'db_password' => 'Password',
        'db_prefix' => 'Table Prefix',
        'host_hint' => 'Usually localhost',
        'port_hint' => 'Usually 3306',
        'name_hint' => 'Database must be created in advance',
        'prefix_hint' => 'Example: bc_',
        'test_btn' => 'Test',
        'connect_btn' => 'Connect',
        'back_btn' => 'Back',
        'checking' => 'Checking...',
        'error' => 'Error',
        'important' => 'Important',
        'permissions_hint' => 'User must have privileges: SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX, DROP on this database',
        'db_empty_required' => 'Database must be empty (no tables)',
        'db_not_exists' => 'Database not found',
        'connection_error' => 'Connection error',
        'install_demo' => 'Install Demo Data',
        'install_demo_desc' => 'If you are installing BloggyCms for the first time, we recommend installing demo data to understand how the system works',
        'db_connection_success' => 'Connection successful! Database is ready for installation.',
        'toggle_password' => 'Show/hide password',
        'connection_success' => 'Connection successful'
    ]
];

$t = $lang[$current_lang];

$errors = [];
$dbConfig = ['host' => 'localhost', 'name' => '', 'user' => '', 'pass' => '', 'prefix' => 'bc_', 'port' => '3306'];
if (isset($_SESSION['db_config'])) $dbConfig = array_merge($dbConfig, $_SESSION['db_config']);
$dbConfig['install_demo'] = isset($_SESSION['db_config']['install_demo']) ? $_SESSION['db_config']['install_demo'] : 0;

if (isset($_SESSION['db_connected']) && $_SESSION['db_connected']) {
    $_SESSION['install_step'] = 3;
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbConfig = array_merge($dbConfig, [
        'host' => $_POST['db_host'] ?? 'localhost',
        'name' => $_POST['db_name'] ?? '',
        'user' => $_POST['db_user'] ?? '',
        'pass' => $_POST['db_pass'] ?? '',
        'prefix' => $_POST['db_prefix'] ?? 'bc_',
        'port' => $_POST['db_port'] ?? '3306',
        'install_demo' => isset($_POST['install_demo']) ? 1 : 0
    ]);
    $_SESSION['db_config'] = $dbConfig;

    if (empty($dbConfig['name'])) {
        $errors[] = $t['db_name'] . ' - ' . ($current_lang == 'ru' ? 'обязательное поле' : 'required field');
    }
    
    if (empty($errors)) {
        try {
            $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, 
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbConfig['name']}'");
            if (!$stmt->fetch()) {
                $errors[] = $t['db_not_exists'] . ": " . htmlspecialchars($dbConfig['name']);
            } else {
                $stmt = $pdo->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = '{$dbConfig['name']}'");
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($result['table_count'] > 0) {
                    $errors[] = $t['db_empty_required'] . " (" . $result['table_count'] . " " . ($current_lang == 'ru' ? 'таблиц найдено' : 'tables found') . ")";
                } else {
                    try {
                        $pdo->exec("USE `{$dbConfig['name']}`");
                        $testTable = "test_cms_install_" . time();
                        $pdo->exec("CREATE TABLE `{$testTable}` (id INT PRIMARY KEY)");
                        $pdo->exec("INSERT INTO `{$testTable}` (id) VALUES (1)");
                        $pdo->query("SELECT id FROM `{$testTable}` WHERE id = 1");
                        $pdo->exec("UPDATE `{$testTable}` SET id = 2 WHERE id = 1");
                        $pdo->exec("DELETE FROM `{$testTable}` WHERE id = 2");
                        $pdo->exec("ALTER TABLE `{$testTable}` ADD COLUMN test_col VARCHAR(255)");
                        $pdo->exec("CREATE INDEX idx_test ON `{$testTable}` (test_col)");
                        $pdo->exec("DROP INDEX idx_test ON `{$testTable}`");
                        $pdo->exec("DROP TABLE `{$testTable}`");
                        
                        $_SESSION['db_connected'] = true;
                        $_SESSION['install_step'] = 3;
                        header('Location: index.php');
                        exit;
                        
                    } catch (PDOException $e) {
                        try {
                            $pdo->exec("DROP TABLE IF EXISTS `{$testTable}`");
                        } catch (Exception $ignored) {}
                        
                        $errorMsg = $e->getMessage();
                        if (strpos($errorMsg, 'CREATE command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется CREATE' : 'Insufficient privileges: CREATE required');
                        } elseif (strpos($errorMsg, 'ALTER command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется ALTER' : 'Insufficient privileges: ALTER required');
                        } elseif (strpos($errorMsg, 'INDEX command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется INDEX' : 'Insufficient privileges: INDEX required');
                        } elseif (strpos($errorMsg, 'DROP command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется DROP' : 'Insufficient privileges: DROP required');
                        } elseif (strpos($errorMsg, 'INSERT command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется INSERT' : 'Insufficient privileges: INSERT required');
                        } elseif (strpos($errorMsg, 'UPDATE command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется UPDATE' : 'Insufficient privileges: UPDATE required');
                        } elseif (strpos($errorMsg, 'DELETE command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется DELETE' : 'Insufficient privileges: DELETE required');
                        } elseif (strpos($errorMsg, 'SELECT command denied') !== false) {
                            $errors[] = ($current_lang == 'ru' ? 'Недостаточно прав: требуется SELECT' : 'Insufficient privileges: SELECT required');
                        } else {
                            $errors[] = $t['connection_error'] . ": " . $e->getMessage();
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            $errors[] = $t['connection_error'] . ": " . $e->getMessage();
        }
    }
}
?>

<h2><?php echo icon('bs', 'database', '24', 'var(--accent)', '', 'style="margin-right: 8px;"'); ?> <?php echo $t['title']; ?></h2>
<p class="step-subtitle"><?php echo $t['subtitle']; ?></p>

<?php if (!empty($errors)) { ?>
    <div class="alert alert-error"><?php echo icon('bs', 'exclamation-circle', '20'); ?><div><strong><?php echo $t['error']; ?></strong><ul style="margin-top:8px;margin-left:20px"><?php foreach($errors as $e) { ?><li><?php echo htmlspecialchars($e); ?></li><?php } ?></ul></div></div>
<?php } ?>

<form method="post" class="needs-validation" id="db-form" novalidate>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['host']; ?> <span class="required">*</span></label>
            <input type="text" name="db_host" class="form-input" value="<?php echo htmlspecialchars($dbConfig['host']); ?>" required>
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['host_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['port']; ?> <span class="required">*</span></label>
            <input type="number" name="db_port" class="form-input" value="<?php echo htmlspecialchars($dbConfig['port']); ?>" required>
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['port_hint']; ?></div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_name']; ?> <span class="required">*</span></label>
            <input type="text" name="db_name" class="form-input" value="<?php echo htmlspecialchars($dbConfig['name']); ?>" required placeholder="myblog_db">
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['name_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_prefix']; ?></label>
            <input type="text" name="db_prefix" class="form-input" value="<?php echo htmlspecialchars($dbConfig['prefix']); ?>" placeholder="bc_">
            <div class="form-hint"><?php echo icon('bs', 'info-circle', '14'); ?> <?php echo $t['prefix_hint']; ?></div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_user']; ?> <span class="required">*</span></label>
            <input type="text" name="db_user" class="form-input" value="<?php echo htmlspecialchars($dbConfig['user']); ?>" required placeholder="myblog_user">
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_password']; ?></label>
            <div class="password-wrapper">
                <input type="password" name="db_pass" class="form-input" value="<?php echo htmlspecialchars($dbConfig['pass']); ?>" id="db_pass">
                <button type="button" class="password-toggle" title="<?php echo $t['toggle_password']; ?>">
                    <?php echo icon('bs', 'eye', '16'); ?>
                </button>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <div class="form-check" style="background: var(--surface-alt); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
            <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 8px;">
                <input type="checkbox" name="install_demo" id="install_demo" value="1" <?php echo !empty($dbConfig['install_demo']) ? 'checked' : ''; ?> style="display: none;">
                <div class="toggle-switch">
                    <span class="toggle-slider"></span>
                </div>
                <span class="form-check-label" style="font-weight: 600; color: var(--text-primary); cursor: pointer;">
                    <?php echo $t['install_demo']; ?>
                </span>
            </label>
            <div class="form-hint" style="margin-top: 8px;"> 
                <?php echo $t['install_demo_desc']; ?>
            </div>
        </div>
    </div>

    <div class="alert alert-info" style="margin-top:24px">
        <?php echo icon('bs', 'info-circle', '14'); ?>
        <div><strong><?php echo $t['important']; ?>:</strong> <?php echo $t['permissions_hint']; ?></div>
    </div>
    <div class="mt-4 flex-between">
        <a href="?restart=1" class="btn btn-outline"><?php echo icon('bs', 'arrow-left', '16'); ?> <?php echo $t['back_btn']; ?></a>
        <div class="flex">
            <button type="button" id="test-connection" class="btn btn-secondary"><?php echo icon('bs', 'plug', '16'); ?> <?php echo $t['test_btn']; ?></button>
            <button type="submit" class="btn btn-primary"><?php echo $t['connect_btn']; ?> <?php echo icon('bs', 'arrow-right', '16'); ?></button>
        </div>
    </div>
</form>

<script>
    document.querySelectorAll('.password-toggle').forEach(btn => btn.addEventListener('click', function() {
        const input = this.previousElementSibling;
        const icon = this.querySelector('svg use');
        if (input.type === 'password') { 
            input.type = 'text'; 
            if (icon) icon.setAttribute('href', '../templates/default/admin/icons/bs.svg#eye-slash');
        } else { 
            input.type = 'password'; 
            if (icon) icon.setAttribute('href', '../templates/default/admin/icons/bs.svg#eye');
        }
    }));

    document.getElementById('db-form')?.addEventListener('submit', function(e) {
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner"></span> <?php echo $t['checking']; ?>...';
    });

    document.getElementById('test-connection')?.addEventListener('click', function() {
        const form = document.getElementById('db-form');
        const formData = new FormData(form);
        
        this.disabled = true;
        this.innerHTML = '<span class="spinner"></span> <?php echo $t['checking']; ?>...';
        
        fetch('ajax/test-connection.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php echo $t['connection_success']; ?>');
            } else {
                alert('<?php echo $t['connection_error']; ?>: ' + data.message);
            }
        })
        .catch(error => {
            alert('<?php echo $t['connection_error']; ?>: ' + error);
        })
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<?php echo icon('bs', 'plug', '16'); ?> <?php echo $t['test_btn']; ?>';
        });
    });
</script>