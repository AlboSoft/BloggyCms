<?php
session_start();

$lang_data = [
    'ru' => [
        'title_lang' => 'Язык установщика',
        'desc_lang' => 'Выберите язык, на котором будет отображаться процесс установки',
        'hint_lang' => 'Этот язык будет использоваться только во время установки',
        'title_check' => 'Проверка системы',
        'desc_check' => 'Сначала убедимся, что ваш сервер соответствует требованиям',
        'required' => 'Требуется',
        'continue' => 'Продолжить',
        'all_good' => 'Всё отлично!',
        'server_ready' => 'Ваш сервер полностью готов к установке',
        'fix_errors' => 'Исправьте ошибки',
        'php_version' => 'Версия PHP',
        'enabled' => 'Включено',
        'disabled' => 'Отключено',
        'writable' => 'Доступно',
        'not_writable' => 'Нет доступа',
        'dir_uploads' => 'Папка uploads',
        'dir_config' => 'Папка system/config',
        'dir_templates' => 'Папка templates'
    ],
    'en' => [
        'title_lang' => 'Installer Language',
        'desc_lang' => 'Select the language for the installation process',
        'hint_lang' => 'This language will be used only during installation',
        'title_check' => 'System Check',
        'desc_check' => 'First, let\'s make sure your server meets the requirements',
        'required' => 'Required',
        'continue' => 'Continue',
        'all_good' => 'All good!',
        'server_ready' => 'Your server is fully ready for installation',
        'fix_errors' => 'Fix errors',
        'php_version' => 'PHP Version',
        'enabled' => 'Enabled',
        'disabled' => 'Disabled',
        'writable' => 'Writable',
        'not_writable' => 'Not writable',
        'dir_uploads' => 'Uploads folder',
        'dir_config' => 'Config folder',
        'dir_templates' => 'Templates folder'
    ]
];

$current_lang = 'ru';
if (isset($_GET['lang']) && isset($lang_data[$_GET['lang']])) {
    $_SESSION['install_lang'] = $_GET['lang'];
    $current_lang = $_GET['lang'];
} elseif (isset($_SESSION['install_lang']) && isset($lang_data[$_SESSION['install_lang']])) {
    $current_lang = $_SESSION['install_lang'];
}

$t = $lang_data[$current_lang];

$requirements = [];
$allPassed = true;

$phpVersion = phpversion();
$phpRequired = '8.0.0';
$phpPassed = version_compare($phpVersion, $phpRequired, '>=');
$requirements[] = [
    'name' => $t['php_version'],
    'required' => $phpRequired,
    'current' => $phpVersion,
    'passed' => $phpPassed
];
if (!$phpPassed) $allPassed = false;

$extensions = [
    'pdo_mysql' => 'PDO MySQL',
    'mysqli' => 'MySQLi',
    'mbstring' => 'mbstring',
    'json' => 'JSON',
    'fileinfo' => 'Fileinfo',
    'session' => 'Session',
    'openssl' => 'OpenSSL'
];
foreach ($extensions as $ext => $name) {
    $loaded = extension_loaded($ext);
    $requirements[] = [
        'name' => $name,
        'required' => $t['enabled'],
        'current' => $loaded ? $t['enabled'] : $t['disabled'],
        'passed' => $loaded
    ];
    if (!$loaded) $allPassed = false;
}

$writableDirs = [
    '../uploads' => $t['dir_uploads'],
    '../system/config' => $t['dir_config'],
    '../templates' => $t['dir_templates']
];
foreach ($writableDirs as $dir => $name) {
    if (!file_exists($dir)) @mkdir($dir, 0755, true);
    $isWritable = is_writable($dir);
    $requirements[] = [
        'name' => $name,
        'required' => $t['writable'],
        'current' => $isWritable ? $t['writable'] : $t['not_writable'],
        'passed' => $isWritable
    ];
    if (!$isWritable) $allPassed = false;
}

if (isset($_POST['next']) && $allPassed) {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
}
?>

<div style="background: #f0f7ff; border-radius: 12px; padding: 20px; margin-bottom: 30px; border: 1px solid #d0e0ff;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 5px;">
                <i class="fas fa-globe" style="color: #3498db; font-size: 18px;"></i>
                <h3 style="margin: 0; font-size: 1rem; color: #2c3e50;"><?php echo $t['title_lang']; ?></h3>
            </div>
            <p style="color: #6c757d; font-size: 0.8rem; margin: 0;"><?php echo $t['desc_lang']; ?></p>
        </div>
        
        <div style="display: flex; gap: 10px;">
            <a href="?lang=ru" style="display: inline-block; padding: 8px 20px; background: <?php echo $current_lang == 'ru' ? '#3498db' : '#ffffff'; ?>; color: <?php echo $current_lang == 'ru' ? '#ffffff' : '#333333'; ?>; border: 1px solid <?php echo $current_lang == 'ru' ? '#3498db' : '#dddddd'; ?>; border-radius: 6px; text-decoration: none; font-size: 14px; cursor: pointer;">Русский</a>
            <a href="?lang=en" style="display: inline-block; padding: 8px 20px; background: <?php echo $current_lang == 'en' ? '#3498db' : '#ffffff'; ?>; color: <?php echo $current_lang == 'en' ? '#ffffff' : '#333333'; ?>; border: 1px solid <?php echo $current_lang == 'en' ? '#3498db' : '#dddddd'; ?>; border-radius: 6px; text-decoration: none; font-size: 14px; cursor: pointer;">English</a>
        </div>
    </div>
    <div class="form-hint" style="margin-top: 12px; font-size: 0.7rem; color: #6c757d; text-align: right;">
        <i class="fas fa-info-circle"></i> <?php echo $t['hint_lang']; ?>
    </div>
</div>

<h2><i class="fas fa-server" style="color: var(--accent); margin-right: 8px;"></i> <?php echo $t['title_check']; ?></h2>
<p class="step-subtitle"><?php echo $t['desc_check']; ?></p>

<?php if (!$allPassed) { ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <div><strong>Problems found</strong><p style="margin-top:4px">Fix the errors below and refresh the page</p></div>
    </div>
<?php } else { ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <div><strong><?php echo $t['all_good']; ?></strong><p style="margin-top:4px"><?php echo $t['server_ready']; ?></p></div>
    </div>
<?php } ?>

<div class="requirements-list">
    <?php foreach ($requirements as $req) { ?>
        <div class="requirement-item">
            <div class="requirement-info">
                <h4><?php echo htmlspecialchars($req['name']); ?></h4>
                <div class="requirement-detail"><?php echo $t['required']; ?>: <?php echo htmlspecialchars($req['required']); ?></div>
            </div>
            <div class="requirement-status">
                <span class="status-badge <?php echo $req['passed'] ? 'success' : 'error'; ?>"><?php echo htmlspecialchars($req['current']); ?></span>
                <div class="status-icon <?php echo $req['passed'] ? 'success' : 'error'; ?>"><i class="fas fa-<?php echo $req['passed'] ? 'check' : 'times'; ?>"></i></div>
            </div>
        </div>
    <?php } ?>
</div>

<div class="mt-4 flex-between">
    <?php if ($allPassed) { ?>
        <form method="post">
            <button type="submit" name="next" class="btn btn-primary"><?php echo $t['continue']; ?> <i class="fas fa-arrow-right"></i></button>
        </form>
    <?php } else { ?>
        <button class="btn btn-primary" disabled><i class="fas fa-exclamation-triangle"></i> <?php echo $t['fix_errors']; ?></button>
    <?php } ?>
</div>