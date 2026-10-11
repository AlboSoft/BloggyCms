<?php

/**
* Контроллер управления языковыми настройками
*/
class LanguageController extends Controller {
    
    public function __construct($db) {
        parent::__construct($db);
    }
    
    public function indexAction() {
        $this->addBreadcrumb(LANG_CONTROLLER_LANGUAGE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_CONTROLLER_LANGUAGE_BREADCRUMB_LANGUAGE);
        
        $settings = $this->getLanguageSettings();
        
        $this->render('admin/language/index', [
            'settings' => $settings,
            'availableLocales' => $this->getAvailableLocales(),
            'pageTitle' => LANG_CONTROLLER_LANGUAGE_PAGE_TITLE
        ]);
    }
    
    public function saveAction() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Notification::error(LANG_CONTROLLER_LANGUAGE_INVALID_METHOD);
            $this->redirect(ADMIN_URL . '/language');
            return;
        }
        
        try {
            $adminLanguage = $_POST['admin_language'] ?? 'ru_RU';
            $siteLanguage = $_POST['site_language'] ?? 'ru_RU';
            $allowUserSwitch = isset($_POST['allow_user_switch']) ? 1 : 0;

            $availableLocales = $this->getAvailableLocales();
            if (!isset($availableLocales[$adminLanguage]) || !isset($availableLocales[$siteLanguage])) {
                throw new Exception(LANG_CONTROLLER_LANGUAGE_INVALID_LOCALE);
            }
            
            $generalSettings = $this->settingsModel->get('general');
            $oldAdminLanguage = $generalSettings['admin_language'] ?? 'ru_RU';
            $generalSettings['admin_language'] = $adminLanguage;
            $generalSettings['site_language'] = $siteLanguage;
            $generalSettings['allow_user_language_switch'] = $allowUserSwitch;
            $this->settingsModel->save('general', $generalSettings);
            
            $languageChanged = ($adminLanguage != $oldAdminLanguage);
            if ($languageChanged) {
                $_SESSION['admin_language'] = $adminLanguage;
            }
            
            Notification::success(LANG_CONTROLLER_LANGUAGE_SAVE_SUCCESS);
            
            if ($languageChanged) {
                $languageDisplayName = ($adminLanguage === 'en_En') ? LANG_CONTROLLER_LANGUAGE_ENGLISH : LANG_CONTROLLER_LANGUAGE_RUSSIAN;
                
                echo '<!DOCTYPE html>
                    <html lang="ru">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>' . LANG_CONTROLLER_LANGUAGE_RELOAD_TITLE . ' | BloggyCMS</title>
                        <meta http-equiv="refresh" content="2;url=' . ADMIN_URL . '/language">
                        <style>
                            * {
                                margin: 0;
                                padding: 0;
                                box-sizing: border-box;
                            }
                            
                            body {
                                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
                                background: #f5f7fa;
                                min-height: 100vh;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                padding: 20px;
                            }
                            
                            .language-switch-card {
                                background: #ffffff;
                                border-radius: 8px;
                                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1), 0 1px 2px rgba(0, 0, 0, 0.06);
                                max-width: 480px;
                                width: 100%;
                                padding: 40px 36px;
                                text-align: center;
                                border: 1px solid #e5e7eb;
                            }
                            
                            .success-icon {
                                width: 56px;
                                height: 56px;
                                margin: 0 auto 20px;
                                background: #10b981;
                                border-radius: 50%;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            
                            .success-icon svg {
                                width: 28px;
                                height: 28px;
                            }
                            
                            h2 {
                                font-size: 24px;
                                font-weight: 600;
                                color: #111827;
                                margin-bottom: 8px;
                            }
                            
                            .language-name {
                                font-size: 14px;
                                color: #6b7280;
                                font-weight: 500;
                                margin-bottom: 24px;
                                padding: 4px 12px;
                                background: #f3f4f6;
                                display: inline-block;
                                border-radius: 4px;
                            }
                            
                            .message-text {
                                color: #4b5563;
                                font-size: 14px;
                                line-height: 1.5;
                                margin-bottom: 28px;
                            }
                            
                            .spinner {
                                width: 32px;
                                height: 32px;
                                border: 2px solid #e5e7eb;
                                border-top-color: #3b82f6;
                                border-radius: 50%;
                                animation: spin 0.8s linear infinite;
                                margin: 0 auto 20px;
                            }
                            
                            @keyframes spin {
                                to { transform: rotate(360deg); }
                            }
                            
                            .progress-bar {
                                width: 100%;
                                height: 2px;
                                background: #e5e7eb;
                                border-radius: 2px;
                                overflow: hidden;
                                margin: 20px 0;
                            }
                            
                            .progress-fill {
                                width: 100%;
                                height: 100%;
                                background: #3b82f6;
                                animation: progress 2s linear forwards;
                            }
                            
                            @keyframes progress {
                                from { width: 0%; }
                                to { width: 100%; }
                            }
                            
                            .redirect-link {
                                color: #3b82f6;
                                text-decoration: none;
                                font-size: 13px;
                                transition: color 0.15s;
                            }
                            
                            .redirect-link:hover {
                                color: #2563eb;
                                text-decoration: underline;
                            }
                            
                            .footer-note {
                                margin-top: 24px;
                                padding-top: 20px;
                                border-top: 1px solid #e5e7eb;
                                font-size: 11px;
                                color: #9ca3af;
                                letter-spacing: 0.3px;
                            }
                        </style>
                    </head>
                    <body>
                        <div class="language-switch-card">
                            <div class="success-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            
                            <h2>' . LANG_CONTROLLER_LANGUAGE_RELOAD_HEADER . '</h2>
                            
                            <div class="language-name">
                                ' . $languageDisplayName . '
                            </div>
                            
                            <div class="message-text">
                                ' . LANG_CONTROLLER_LANGUAGE_RELOAD_MESSAGE . '<br>
                                ' . LANG_CONTROLLER_LANGUAGE_RELOAD_WAIT . '
                            </div>
                            
                            <div class="spinner"></div>
                            
                            <div class="progress-bar">
                                <div class="progress-fill"></div>
                            </div>
                            
                            <div>
                                <a href="' . ADMIN_URL . '/language" class="redirect-link">' . LANG_CONTROLLER_LANGUAGE_RELOAD_NOW . '</a>
                            </div>
                            
                            <div class="footer-note">
                                BloggyCMS
                            </div>
                        </div>
                        
                        <script>
                            let seconds = 2;
                            const redirectUrl = "' . ADMIN_URL . '/language";
                            
                            const timer = setInterval(function() {
                                seconds--;
                                if (seconds <= 0) {
                                    clearInterval(timer);
                                    window.location.href = redirectUrl;
                                }
                            }, 1000);
                        </script>
                    </body>
                    </html>';
                exit;
            }
            
        } catch (Exception $e) {
            Notification::error(LANG_CONTROLLER_LANGUAGE_SAVE_ERROR . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/language');
    }
    
    private function getLanguageSettings() {
        $generalSettings = $this->settingsModel->get('general');
        
        return [
            'admin_language' => $generalSettings['admin_language'] ?? 'ru_RU',
            'site_language' => $generalSettings['site_language'] ?? 'ru_RU',
            'allow_user_switch' => $generalSettings['allow_user_language_switch'] ?? false
        ];
    }
    
    /**
    * Получение списка доступных языков из папки system/languages
    * Название языка берется из файла core/Language.php каждого пакета
    */
    private function getAvailableLocales() {
        $locales = [];
        $langPath = SYSTEM_PATH . '/languages';
        
        if (!is_dir($langPath)) {
            return $locales;
        }
        
        $dirs = @scandir($langPath) ?: [];
        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') continue;
            if (!is_dir($langPath . '/' . $dir)) continue;
            
            $langName = $dir;
            $langFile = $langPath . '/' . $dir . '/core/Language.php';
            
            if (file_exists($langFile)) {
                $langData = include $langFile;
                if (is_array($langData) && isset($langData['name'])) {
                    $langName = $langData['name'];
                }
            }
            
            $locales[$dir] = [
                'code' => $dir,
                'name' => $langName
            ];
        }
        
        return $locales;
    }
}