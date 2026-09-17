<?php
session_start();

require_once __DIR__ . '/helpers.php';

$current_lang = $_SESSION['install_lang'] ?? 'ru';

if (isset($_SESSION['install_step']) && $_SESSION['install_step'] > 1) {
    header('Location: index.php');
    exit;
}

$lang = [
    'ru' => [
        'kicker'     => 'Установка',
        'hello'      => 'Привет.',
        'welcome'    => 'Вас приветствует мастер установки',
        'brand'      => 'BloggyCMS',
        'tagline'    => 'Современный блог — это просто. Сейчас настроим ваш блог: подключим базу данных, создадим аккаунт администратора и запустим сайт. Займёт около двух минут.',
        'features'   => 'Что вас ждёт',
        'feat1_title'=> 'Быстрая установка',
        'feat1_desc' => 'Четыре шага и ваш блог готов к работе.',
        'feat2_title'=> 'Гибкая настройка',
        'feat2_desc' => 'База данных, языки интерфейса, аккаунт администратора.',
        'feat3_title'=> 'Демо-данные',
        'feat3_desc' => 'Опционально установим пример контента для старта.',
        'start'      => 'Начать установку',
        'hint'       => 'Шаг 1 из 4 · Проверка окружения',
    ],
    'en' => [
        'kicker'     => 'Setup',
        'hello'      => 'Hello.',
        'welcome'    => 'Welcome to the installation wizard',
        'brand'      => 'BloggyCMS',
        'tagline'    => 'A modern blog made simple. We will connect the database, create an administrator account and launch your site. It takes about two minutes.',
        'features'   => 'What is ahead',
        'feat1_title'=> 'Quick install',
        'feat1_desc' => 'Four steps and your blog is ready to go.',
        'feat2_title'=> 'Flexible setup',
        'feat2_desc' => 'Database, interface languages, administrator account.',
        'feat3_title'=> 'Demo data',
        'feat3_desc' => 'Optionally install sample content to get started.',
        'start'      => 'Start installation',
        'hint'       => 'Step 1 of 4 · Environment check',
    ]
];

$t = $lang[$current_lang];

if (isset($_POST['start_install'])) {
    $_SESSION['install_step'] = 1;
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка BloggyCMS</title>
    <link rel="stylesheet" href="assets/css/welcome.css">
</head>
<body>

    <header class="topbar">
        <div class="topbar-inner">
            <a href="?restart=1" class="brand">
                <span class="brand-mark"><?php echo icon('bs', 'pen', '15', '#fff'); ?></span>
                Bloggy<span class="brand-accent">CMS</span>
                <span class="brand-badge">setup</span>
            </a>
            <nav class="langs">
                <a href="?lang=ru" class="<?php echo $current_lang === 'ru' ? 'active' : ''; ?>">RU</a>
                <a href="?lang=en" class="<?php echo $current_lang === 'en' ? 'active' : ''; ?>">EN</a>
            </nav>
        </div>
    </header>

    <main class="page">

        <p class="kicker"><?php echo $t['kicker']; ?></p>

        <h1 class="title">
            <span class="hello"><?php echo $t['hello']; ?></span>
            <?php echo $t['welcome']; ?>
            <span class="brand-inline"><?php echo $t['brand']; ?></span>
        </h1>

        <p class="lead"><?php echo $t['tagline']; ?></p>

        <section class="section">
            <h2 class="section-title"><?php echo $t['features']; ?></h2>

            <div class="features">
                <div class="feature">
                    <div class="feature-icon">
                        <?php echo icon('bs', 'rocket-takeoff', '18', 'var(--accent)'); ?>
                    </div>
                    <h4><?php echo $t['feat1_title']; ?></h4>
                    <p><?php echo $t['feat1_desc']; ?></p>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <?php echo icon('bs', 'sliders', '18', 'var(--accent)'); ?>
                    </div>
                    <h4><?php echo $t['feat2_title']; ?></h4>
                    <p><?php echo $t['feat2_desc']; ?></p>
                </div>

                <div class="feature">
                    <div class="feature-icon">
                        <?php echo icon('bs', 'box-seam', '18', 'var(--accent)'); ?>
                    </div>
                    <h4><?php echo $t['feat3_title']; ?></h4>
                    <p><?php echo $t['feat3_desc']; ?></p>
                </div>
            </div>
        </section>

        <form method="post" class="action">
            <button type="submit" name="start_install" class="btn-primary">
                <?php echo icon('bs', 'arrow-right', '16', '#fff'); ?>
                <?php echo $t['start']; ?>
            </button>
            <span class="hint"><?php echo $t['hint']; ?></span>
        </form>

    </main>

    <footer class="footer">
        <span>© <?php echo date('Y'); ?> BloggyCMS</span>
        <a href="https://github.com/AlboSoft/BloggyCms" target="_blank" rel="noopener">
            github.com/AlboSoft/BloggyCms
        </a>
    </footer>

</body>
</html>