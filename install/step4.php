<?php
session_start();

$current_lang = $_SESSION['install_lang'] ?? 'ru';

$lang = [
    'ru' => [
        'title' => 'Установка завершена!',
        'congrats' => 'Поздравляем! BloggyCMS успешно установлен и готов к работе',
        'your_blog' => 'Ваш блог',
        'administrator' => 'Администратор',
        'name' => 'Название',
        'url' => 'URL',
        'login' => 'Логин',
        'email' => 'Email',
        'security_warning' => 'Важно для безопасности!',
        'delete_install_folder' => 'Удалите папку /install с сервера после завершения настройки',
        'restart' => 'Начать заново',
        'go_to_site' => 'На сайт',
        'go_to_admin' => 'В админку'
    ],
    'en' => [
        'title' => 'Installation Complete!',
        'congrats' => 'Congratulations! BloggyCMS has been successfully installed and is ready to use',
        'your_blog' => 'Your Blog',
        'administrator' => 'Administrator',
        'name' => 'Name',
        'url' => 'URL',
        'login' => 'Login',
        'email' => 'Email',
        'security_warning' => 'Security Important!',
        'delete_install_folder' => 'Delete the /install folder from the server after completing the setup',
        'restart' => 'Restart',
        'go_to_site' => 'Go to Site',
        'go_to_admin' => 'Go to Admin'
    ]
];

$t = $lang[$current_lang];

if (!isset($_SESSION['site_config']) || !isset($_SESSION['db_config'])) {
    $_SESSION['install_step'] = 1;
    header('Location: index.php');
    exit;
}
$siteConfig = $_SESSION['site_config'];
$dbConfig = $_SESSION['db_config'];
?>

<h2><?php echo icon('bs', 'flag', '24', 'var(--accent)', '', 'style="margin-right: 8px;"'); ?> <?php echo $t['title']; ?></h2>

<div class="alert alert-success" style="margin-bottom: 28px;">
    <?php echo icon('bs', 'check-circle-fill', '20', '#27ae60'); ?>
    <div>
        <strong><?php echo $t['congrats']; ?></strong>
    </div>
</div>

<div class="summary-grid">
    <div class="summary-card">
        <h4><?php echo icon('bs', 'globe2', '16'); ?> <?php echo $t['your_blog']; ?></h4>
        <div class="summary-item">
            <span class="label"><?php echo $t['name']; ?></span>
            <span class="value"><?php echo htmlspecialchars($siteConfig['site_name']); ?></span>
        </div>
        <div class="summary-item">
            <span class="label"><?php echo $t['url']; ?></span>
            <span class="value">
                <a href="<?php echo htmlspecialchars($siteConfig['site_url']); ?>" target="_blank">
                    <?php echo htmlspecialchars($siteConfig['site_url']); ?> 
                    <?php echo icon('bs', 'box-arrow-up-right', '12', '', '', 'style="font-size: 0.7em; margin-left: 4px;"'); ?>
                </a>
            </span>
        </div>
    </div>
    
    <div class="summary-card">
        <h4><?php echo icon('bs', 'person-badge', '16'); ?> <?php echo $t['administrator']; ?></h4>
        <div class="summary-item">
            <span class="label"><?php echo $t['login']; ?></span>
            <span class="value"><?php echo htmlspecialchars($siteConfig['admin_username']); ?></span>
        </div>
        <div class="summary-item">
            <span class="label"><?php echo $t['email']; ?></span>
            <span class="value"><?php echo htmlspecialchars($siteConfig['admin_email']); ?></span>
        </div>
    </div>
</div>

<div class="alert alert-warning" style="margin: 28px 0;">
    <?php echo icon('bs', 'exclamation-triangle', '20'); ?>
    <div>
        <strong><?php echo $t['security_warning']; ?></strong><br>
        <?php echo $t['delete_install_folder']; ?>
    </div>
</div>

<div class="mt-4 flex-between">
    <a href="?restart=1" class="btn btn-outline">
        <?php echo icon('bs', 'arrow-repeat', '16'); ?> <?php echo $t['restart']; ?>
    </a>
    <div class="flex">
        <a href="<?php echo htmlspecialchars($siteConfig['site_url']); ?>" class="btn btn-secondary" target="_blank">
            <?php echo icon('bs', 'house-door', '16'); ?> <?php echo $t['go_to_site']; ?>
        </a>
        <a href="<?php echo htmlspecialchars($siteConfig['site_url']); ?>/admin" class="btn btn-primary" target="_blank">
            <?php echo icon('bs', 'box-arrow-in-right', '16'); ?> <?php echo $t['go_to_admin']; ?>
        </a>
    </div>
</div>