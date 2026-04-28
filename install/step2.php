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
        'name_hint' => 'Будет создана автоматически',
        'prefix_hint' => 'Например: bc_',
        'test_btn' => 'Проверить',
        'connect_btn' => 'Подключить',
        'back_btn' => 'Назад',
        'checking' => 'Проверка...',
        'error' => 'Ошибка',
        'important' => 'Важно',
        'permissions_hint' => 'Пользователь должен иметь права на создание БД и таблиц',
        'db_has_tables' => 'База данных уже содержит таблицы.',
        'connection_error' => 'Ошибка подключения',
        'install_demo' => 'Установить демо данные',
        'install_demo_desc' => 'Будут добавлены: 3 категории, 3 статьи, меню, страницы, настройки и теги для быстрого старта'
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
        'name_hint' => 'Will be created automatically',
        'prefix_hint' => 'Example: bc_',
        'test_btn' => 'Test',
        'connect_btn' => 'Connect',
        'back_btn' => 'Back',
        'checking' => 'Checking...',
        'error' => 'Error',
        'important' => 'Important',
        'permissions_hint' => 'User must have privileges to create database and tables',
        'db_has_tables' => 'Database already contains tables.',
        'connection_error' => 'Connection error',
        'install_demo' => 'Install Demo Data',
        'install_demo_desc' => 'Will add: 3 categories, 3 posts, menu, pages, settings and tags for quick start'
    ]
];

$t = $lang[$current_lang];

$errors = [];
$dbConfig = ['host' => 'localhost', 'name' => 'bloggycms', 'user' => 'root', 'pass' => '', 'prefix' => 'bc_', 'port' => '3306'];
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
        'name' => $_POST['db_name'] ?? 'bloggycms',
        'user' => $_POST['db_user'] ?? 'root',
        'pass' => $_POST['db_pass'] ?? '',
        'prefix' => $_POST['db_prefix'] ?? 'bc_',
        'port' => $_POST['db_port'] ?? '3306',
        'install_demo' => isset($_POST['install_demo']) ? 1 : 0
    ]);
    $_SESSION['db_config'] = $dbConfig;

    try {
        $dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset=utf8mb4";
        $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
        
        $stmt = $pdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbConfig['name']}'");
        if ($stmt->fetch()) {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = '{$dbConfig['name']}'");
            if ($stmt->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                $errors[] = $t['db_has_tables'];
            } else {
                $_SESSION['db_connected'] = true;
                $_SESSION['install_step'] = 3;
                header('Location: index.php');
                exit;
            }
        } else {
            $pdo->exec("CREATE DATABASE `{$dbConfig['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $_SESSION['db_connected'] = true;
            $_SESSION['install_step'] = 3;
            header('Location: index.php');
            exit;
        }
    } catch (PDOException $e) {
        $errors[] = $t['connection_error'] . ": " . $e->getMessage();
    }
}
?>

<h2><i class="fas fa-database" style="color: var(--accent); margin-right: 8px;"></i> <?php echo $t['title']; ?></h2>
<p class="step-subtitle"><?php echo $t['subtitle']; ?></p>

<?php if (!empty($errors)) { ?>
    <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i><div><strong><?php echo $t['error']; ?></strong><ul style="margin-top:8px;margin-left:20px"><?php foreach($errors as $e) { ?><li><?php echo htmlspecialchars($e); ?></li><?php } ?></ul></div></div>
<?php } ?>

<form method="post" class="needs-validation" id="db-form" novalidate>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['host']; ?> <span class="required">*</span></label>
            <input type="text" name="db_host" class="form-input" value="<?php echo htmlspecialchars($dbConfig['host']); ?>" required>
            <div class="form-hint"><i class="fas fa-info-circle"></i> <?php echo $t['host_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['port']; ?> <span class="required">*</span></label>
            <input type="number" name="db_port" class="form-input" value="<?php echo htmlspecialchars($dbConfig['port']); ?>" required>
            <div class="form-hint"><i class="fas fa-info-circle"></i> <?php echo $t['port_hint']; ?></div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_name']; ?> <span class="required">*</span></label>
            <input type="text" name="db_name" class="form-input" value="<?php echo htmlspecialchars($dbConfig['name']); ?>" required>
            <div class="form-hint"><i class="fas fa-info-circle"></i> <?php echo $t['name_hint']; ?></div>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_prefix']; ?></label>
            <input type="text" name="db_prefix" class="form-input" value="<?php echo htmlspecialchars($dbConfig['prefix']); ?>">
            <div class="form-hint"><i class="fas fa-info-circle"></i> <?php echo $t['prefix_hint']; ?></div>
        </div>
    </div>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_user']; ?> <span class="required">*</span></label>
            <input type="text" name="db_user" class="form-input" value="<?php echo htmlspecialchars($dbConfig['user']); ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label"><?php echo $t['db_password']; ?></label>
            <div class="password-wrapper">
                <input type="password" name="db_pass" class="form-input"
                       value="<?php echo htmlspecialchars($dbConfig['pass']); ?>" id="db_pass">
                <button type="button" class="password-toggle" title="<?php echo $t['toggle_password'] ?? 'Показать/скрыть пароль'; ?>">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="form-group mt-3">
        <div class="form-check" style="background: var(--surface-alt); padding: 16px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
            <input type="checkbox" name="install_demo" id="install_demo" class="form-check-input" value="1" <?php echo !empty($dbConfig['install_demo']) ? 'checked' : ''; ?>>
            <label class="form-check-label" for="install_demo" style="font-weight: 600; color: var(--text-primary); cursor: pointer;">
                <i class="fas fa-seedling" style="color: var(--accent); margin-right: 6px;"></i>
                <?php echo $t['install_demo']; ?>
            </label>
            <div class="form-hint" style="margin-top: 8px; margin-left: 24px;">
                <i class="fas fa-info-circle"></i> 
                <?php echo $t['install_demo_desc']; ?>
            </div>
        </div>
    </div>

    <div class="alert alert-info" style="margin-top:24px">
        <i class="fas fa-info-circle"></i>
        <div><strong><?php echo $t['important']; ?>:</strong> <?php echo $t['permissions_hint']; ?></div>
    </div>
    <div class="mt-4 flex-between">
        <a href="?restart=1" class="btn btn-outline"><i class="fas fa-arrow-left"></i> <?php echo $t['back_btn']; ?></a>
        <div class="flex">
            <button type="button" id="test-connection" class="btn btn-secondary"><i class="fas fa-plug"></i> <?php echo $t['test_btn']; ?></button>
            <button type="submit" class="btn btn-primary"><?php echo $t['connect_btn']; ?> <i class="fas fa-arrow-right"></i></button>
        </div>
    </div>
</form>

<script>
document.querySelectorAll('.password-toggle').forEach(btn => btn.addEventListener('click', function() {
    const input = this.previousElementSibling;
    const icon = this.querySelector('i');
    if (input.type === 'password') { input.type = 'text'; icon.classList.replace('fa-eye','fa-eye-slash'); }
    else { input.type = 'password'; icon.classList.replace('fa-eye-slash','fa-eye'); }
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
            alert('<?php echo $t['connection_success'] ?? 'Подключение успешно'; ?>');
        } else {
            alert('<?php echo $t['connection_error']; ?>: ' + data.message);
        }
    })
    .catch(error => {
        alert('<?php echo $t['connection_error']; ?>: ' + error);
    })
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-plug"></i> <?php echo $t['test_btn']; ?>';
    });
});
</script>