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
        'dir_templates' => 'Папка templates',
        'license_title' => 'Лицензионное соглашение',
        'agree_license' => 'Я принимаю условия лицензионного соглашения',
        'license_accept_error' => 'Для продолжения установки необходимо принять условия лицензии',
        'license_text_title' => 'MIT License',
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
        'dir_templates' => 'Templates folder',
        'license_title' => 'License Agreement',
        'agree_license' => 'I agree to the terms of the license agreement',
        'license_accept_error' => 'You must accept the license terms to continue installation',
        'license_text_title' => 'MIT License',
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
$license_accepted = isset($_SESSION['license_accepted']) && $_SESSION['license_accepted'] === true;
$license_error = false;

if (isset($_POST['accept_license'])) {
    $_SESSION['license_accepted'] = true;
    $license_accepted = true;
    header('Location: index.php?step=1');
    exit;
}

$requirements = [];
$allPassed = true;

if ($license_accepted) {
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
}

if (isset($_POST['next']) && $license_accepted && $allPassed) {
    $_SESSION['install_step'] = 2;
    header('Location: index.php');
    exit;
}

if (isset($_POST['next']) && !$license_accepted) {
    $license_error = true;
}
?>

<?php if (!$license_accepted) { ?>
    <div style="background: #fff8e7; border-radius: 12px; padding: 20px; margin-bottom: 30px; border: 1px solid #ffe0a3;">
        <div style="display: flex; align-items: flex-start; gap: 15px; flex-wrap: wrap;">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                    <?php echo icon('bs', 'file-earmark-text', '20', '#e67e22'); ?>
                    <h3 style="margin: 0; font-size: 1.1rem; color: #2c3e50;"><?php echo $t['license_title']; ?></h3>
                </div>
                <div style="background: #f5f5f5; border-radius: 8px; padding: 12px; max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 11px; line-height: 1.4; margin-bottom: 12px; border: 1px solid #e0e0e0;">
                    <strong><?php echo $t['license_text_title']; ?></strong><br><br>
                    Copyright (c) 2026 Pechora.Dev

                    Permission is hereby granted, free of charge, to any person obtaining a copy
                    of this software and associated documentation files (the "Software"), to deal
                    in the Software without restriction, including without limitation the rights
                    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
                    copies of the Software, and to permit persons to whom the Software is
                    furnished to do so, subject to the following conditions:

                    The above copyright notice and this permission notice shall be included in all
                    copies or substantial portions of the Software.

                    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
                    SOFTWARE.
                </div>
                <form method="post" style="margin: 0;">
                    <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; margin-bottom: 15px;">
                        <input type="checkbox" name="accept_license" value="1" required style="display: none;">
                        <div class="toggle-switch">
                            <span class="toggle-slider"></span>
                        </div>
                        <span><?php echo $t['agree_license']; ?></span>
                    </label>
                    <button type="submit" name="submit_license" class="btn btn-primary">
                        <?php echo icon('bs', 'check-lg', '16'); ?> <?php echo $t['continue']; ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div style="text-align: center; padding: 40px 20px;">
        <?php echo icon('bs', 'file-earmark-text', '48', '#e67e22', '', 'style="margin-bottom: 20px;"'); ?>
        <h3><?php echo $t['license_title']; ?></h3>
        <p>Пожалуйста, ознакомьтесь с условиями лицензии выше и примите их, чтобы продолжить установку.</p>
    </div>

<?php } else { ?>

    <h2><?php echo icon('bs', 'server', '24', 'var(--accent)', '', 'style="margin-right: 8px;"'); ?> <?php echo $t['title_check']; ?></h2>
    <p class="step-subtitle"><?php echo $t['desc_check']; ?></p>

    <?php if (!$allPassed) { ?>
        <div class="alert alert-error">
            <?php echo icon('bs', 'exclamation-circle', '20'); ?>
            <div><strong>Problems found</strong><p style="margin-top:4px">Fix the errors below and refresh the page</p></div>
        </div>
    <?php } else { ?>
        <div class="alert alert-success">
            <?php echo icon('bs', 'check-circle-fill', '20', '#27ae60'); ?>
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
                    <div class="status-icon <?php echo $req['passed'] ? 'success' : 'error'; ?>"><?php echo icon('bs', $req['passed'] ? 'check-lg' : 'x-lg', '16'); ?></div>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php if ($license_error) { ?>
        <div class="alert alert-error" style="margin-top: 20px;">
            <?php echo icon('bs', 'exclamation-circle', '20'); ?>
            <div><?php echo $t['license_accept_error']; ?></div>
        </div>
    <?php } ?>

    <div class="mt-4 flex-between">
        <?php if ($allPassed) { ?>
            <form method="post">
                <button type="submit" name="next" class="btn btn-primary"><?php echo $t['continue']; ?> <?php echo icon('bs', 'arrow-right', '16'); ?></button>
            </form>
        <?php } else { ?>
            <button class="btn btn-primary" disabled><?php echo icon('bs', 'exclamation-triangle', '20'); ?> <?php echo $t['fix_errors']; ?></button>
        <?php } ?>
    </div>

<?php } ?>