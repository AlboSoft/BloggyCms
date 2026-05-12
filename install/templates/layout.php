<?php 
require_once __DIR__ . '/../helpers.php';
$current_lang = $_SESSION['install_lang'] ?? 'ru';
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка BloggyCMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="install-page">
    <div class="install-wrapper">
        <div class="install-sidebar">
            <div class="sidebar-bg"></div>
            <div class="sidebar-content">
                <div class="logo-block">
                    <div class="logo-icon">
                        <?php echo icon('bs', 'pen', '16', "#fff"); ?>
                    </div>
                    <h1 class="logo-title">
                        Bloggy<span class="logo-highlight">CMS</span>
                    </h1>
                    <p class="logo-subtitle">Installer</p>
                </div>
                
                <div class="welcome-text">
                    <h2 class="welcome-title"><?php echo $current_lang == 'ru' ? 'Установка блога' : 'Blog Installation'; ?></h2>
                    <p class="welcome-desc">
                        <?php echo $current_lang == 'ru' ? 'Всего 4 простых шага отделяют вас от запуска современного блога на BloggyCMS' : 'Just 4 simple steps separate you from launching a modern blog on BloggyCMS'; ?>
                    </p>
                </div>
                
                <div class="steps-list">
                    <div class="step-item <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step > 1) { ?>
                                <?php echo icon('bs', 'check-lg', '16'); ?>
                            <?php } else { ?>
                                <?php echo icon('bs', 'server', '16'); ?>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name"><?php echo $current_lang == 'ru' ? 'Проверка системы' : 'System Check'; ?></span>
                            <span class="step-desc"><?php echo $current_lang == 'ru' ? 'Требования PHP' : 'PHP Requirements'; ?></span>
                        </div>
                    </div>
                    
                    <div class="step-item <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step > 2) { ?>
                                <?php echo icon('bs', 'check-lg', '16'); ?>
                            <?php } else { ?>
                                <?php echo icon('bs', 'database', '16'); ?>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name"><?php echo $current_lang == 'ru' ? 'База данных' : 'Database'; ?></span>
                            <span class="step-desc"><?php echo $current_lang == 'ru' ? 'Подключение MySQL' : 'MySQL Connection'; ?></span>
                        </div>
                    </div>
                    
                    <div class="step-item <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step > 3) { ?>
                                <?php echo icon('bs', 'check-lg', '16'); ?>
                            <?php } else { ?>
                                <?php echo icon('bs', 'person-badge', '16'); ?>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name"><?php echo $current_lang == 'ru' ? 'Администратор' : 'Administrator'; ?></span>
                            <span class="step-desc"><?php echo $current_lang == 'ru' ? 'Учетная запись' : 'Account'; ?></span>
                        </div>
                    </div>
                    
                    <div class="step-item <?php echo $step >= 4 ? 'active' : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step >= 4) { ?>
                                <?php echo icon('bs', 'check-lg', '16'); ?>
                            <?php } else { ?>
                                <?php echo icon('bs', 'flag', '16'); ?>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name"><?php echo $current_lang == 'ru' ? 'Завершение' : 'Completion'; ?></span>
                            <span class="step-desc"><?php echo $current_lang == 'ru' ? 'Финальный шаг' : 'Final Step'; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-footer">
                    <p>© <?php echo date('Y'); ?> BloggyCMS</p>
                    <a href="https://github.com/pechoradev/BloggyCms" target="_blank" class="btn-github">
                        <?php echo icon('brands', 'github', '18'); ?>
                        <span>GitHub</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="install-form-panel">
            <div class="form-inner">
                <div class="install-card">
                    <div style="display: flex; justify-content: flex-end; gap: 8px; margin-bottom: 20px;">
                        <a href="?lang=ru" style="display: inline-block; padding: 6px 16px; background: <?php echo $current_lang == 'ru' ? '#3498db' : '#f0f0f0'; ?>; color: <?php echo $current_lang == 'ru' ? '#ffffff' : '#333333'; ?>; border-radius: 6px; text-decoration: none; font-size: 13px; cursor: pointer;">Русский</a>
                        <a href="?lang=en" style="display: inline-block; padding: 6px 16px; background: <?php echo $current_lang == 'en' ? '#3498db' : '#f0f0f0'; ?>; color: <?php echo $current_lang == 'en' ? '#ffffff' : '#333333'; ?>; border-radius: 6px; text-decoration: none; font-size: 13px; cursor: pointer;">English</a>
                    </div>
                    
                    <?php include $stepFile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/confetti.js"></script>
    <script src="assets/js/script.js"></script>
    
    <?php if ($step == 4) { ?>
        <script>
            setTimeout(function() {
                confetti({ particleCount: 200, spread: 70, origin: { y: 0.6 } });
                setTimeout(function() { confetti({ particleCount: 150, spread: 100, origin: { y: 0.5 } }); }, 300);
                setTimeout(function() { confetti({ particleCount: 100, spread: 120, origin: { y: 0.4 } }); }, 600);
                setTimeout(function() { confetti({ particleCount: 80, spread: 360, origin: { y: 0.2 } }); }, 1000);
            }, 500);
        </script>
    <?php } ?>
</body>
</html>