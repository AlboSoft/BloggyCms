<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Установка BloggyCMS</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="install-page">
    <div class="install-wrapper">
        <div class="install-sidebar">
            <div class="sidebar-bg"></div>
            <div class="sidebar-content">
                <div class="logo-block">
                    <div class="logo-icon">
                        <i class="fas fa-pen-nib"></i>
                    </div>
                    <h1 class="logo-title">
                        Bloggy<span class="logo-highlight">CMS</span>
                    </h1>
                    <p class="logo-subtitle">Installer</p>
                </div>
                
                <div class="welcome-text">
                    <h2 class="welcome-title">Установка блога</h2>
                    <p class="welcome-desc">
                        Всего 4 простых шага отделяют вас от запуска современного блога на BloggyCMS
                    </p>
                </div>
                
                <div class="steps-list">
                    <div class="step-item <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step > 1) { ?>
                                <i class="fas fa-check"></i>
                            <?php } else { ?>
                                <i class="fas fa-server"></i>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name">Проверка системы</span>
                            <span class="step-desc">Требования PHP</span>
                        </div>
                    </div>
                    
                    <div class="step-item <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step > 2) { ?>
                                <i class="fas fa-check"></i>
                            <?php } else { ?>
                                <i class="fas fa-database"></i>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name">База данных</span>
                            <span class="step-desc">Подключение MySQL</span>
                        </div>
                    </div>
                    
                    <div class="step-item <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step > 3) { ?>
                                <i class="fas fa-check"></i>
                            <?php } else { ?>
                                <i class="fas fa-user-shield"></i>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name">Администратор</span>
                            <span class="step-desc">Учетная запись</span>
                        </div>
                    </div>
                    
                    <div class="step-item <?php echo $step >= 4 ? 'active' : ''; ?>">
                        <div class="step-icon">
                            <?php if ($step >= 4) { ?>
                                <i class="fas fa-check"></i>
                            <?php } else { ?>
                                <i class="fas fa-flag-checkered"></i>
                            <?php } ?>
                        </div>
                        <div class="step-info">
                            <span class="step-name">Завершение</span>
                            <span class="step-desc">Финальный шаг</span>
                        </div>
                    </div>
                </div>
                
                <div class="sidebar-footer">
                    <p>© <?php echo date('Y'); ?> BloggyCMS</p>
                    <a href="https://github.com/pechoradev/BloggyCms" target="_blank" class="btn-github">
                        <i class="fab fa-github"></i>
                        <span>GitHub</span>
                    </a>
                </div>
            </div>
        </div>
        
        <div class="install-form-panel">
            <div class="form-inner">
                <div class="install-card">
                    <?php include $stepFile; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/script.js"></script>
</body>
</html>