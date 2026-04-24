<?php

/**
* Класс для отправки email-уведомлений пользователям
* @package Core
*/
class Email {
    
    /**
    * Отправляет email для восстановления пароля 
    * @param string $email Email получателя
    * @param string $token Токен для сброса пароля
    * @param string $username Имя пользователя
    * @return bool Результат отправки (true/false)
    */
    public static function sendPasswordReset($email, $token, $username) {
        $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost';
        
        $resetLink = $baseUrl . '/reset-password?token=' . $token;
        
        $subject = sprintf(LANG_HELPER_EMAIL_RESET_SUBJECT, $siteName);
        
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>" . LANG_HELPER_EMAIL_RESET_TITLE . "</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; }
                    .button { background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                    .footer { font-size: 12px; color: #666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2 style='color: #333; text-align: center;'>" . LANG_HELPER_EMAIL_RESET_TITLE . "</h2>
                    <p>" . sprintf(LANG_HELPER_EMAIL_RESET_GREETING, $username) . "</p>
                    <p>" . sprintf(LANG_HELPER_EMAIL_RESET_MESSAGE, $siteName) . "</p>
                    <p>" . LANG_HELPER_EMAIL_RESET_INSTRUCTION . "</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' class='button'>
                            " . LANG_HELPER_EMAIL_RESET_BUTTON . "
                        </a>
                    </div>
                    <p>" . LANG_HELPER_EMAIL_RESET_VALIDITY . "</p>
                    <p>" . LANG_HELPER_EMAIL_RESET_IGNORE . "</p>
                    <div class='footer'>
                        <p>" . LANG_HELPER_EMAIL_FOOTER_AUTO . "</p>
                        <p>" . LANG_HELPER_EMAIL_FOOTER_CONTACT . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $siteName . " <" . $siteEmail . ">" . "\r\n";
        $headers .= "Reply-To: " . $siteEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $headers .= "X-Priority: 1 (Highest)" . "\r\n";
        $headers .= "X-MSMail-Priority: High" . "\r\n";
        $headers .= "Importance: High" . "\r\n";
        
        return mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $headers);
    }
    
    /**
    * Отправляет email с уведомлением об успешном изменении пароля 
    * @param string $email Email получателя
    * @param string $username Имя пользователя
    * @return bool Результат отправки
    */
    public static function sendPasswordChanged($email, $username) {
        $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
        
        $subject = sprintf(LANG_HELPER_EMAIL_CHANGED_SUBJECT, $siteName);
        
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>" . LANG_HELPER_EMAIL_CHANGED_TITLE . "</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #333; text-align: center;'>" . LANG_HELPER_EMAIL_CHANGED_TITLE . "</h2>
                    <p>" . sprintf(LANG_HELPER_EMAIL_CHANGED_GREETING, $username) . "</p>
                    <p>" . sprintf(LANG_HELPER_EMAIL_CHANGED_MESSAGE, $siteName) . "</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>" . LANG_HELPER_EMAIL_CHANGED_WARNING . "</strong></p>
                    </div>
                    <p>" . LANG_HELPER_EMAIL_CHANGED_LOGIN_INSTRUCTION . "</p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . BASE_URL . "/login' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            " . LANG_HELPER_EMAIL_CHANGED_LOGIN_BUTTON . "
                        </a>
                    </div>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 12px; color: #666;'>
                        <p>" . LANG_HELPER_EMAIL_FOOTER_AUTO . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $siteEmail . "\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
    
    /**
    * Отправляет приветственное письмо после регистрации
    * @param string $email Email получателя
    * @param string $username Имя пользователя
    * @return bool Результат отправки
    */
    public static function sendWelcomeEmail($email, $username) {
        $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
        
        $subject = sprintf(LANG_HELPER_EMAIL_WELCOME_SUBJECT, $siteName);
        
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>" . LANG_HELPER_EMAIL_WELCOME_TITLE . "</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #333; text-align: center;'>" . sprintf(LANG_HELPER_EMAIL_WELCOME_HEADER, $siteName) . "</h2>
                    <p>" . sprintf(LANG_HELPER_EMAIL_WELCOME_GREETING, $username) . "</p>
                    <p>" . LANG_HELPER_EMAIL_WELCOME_MESSAGE . "</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . BASE_URL . "/login' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            " . LANG_HELPER_EMAIL_WELCOME_BUTTON . "
                        </a>
                    </div>
                    <p>" . LANG_HELPER_EMAIL_WELCOME_SUPPORT . "</p>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 12px; color: #666;'>
                        <p>" . LANG_HELPER_EMAIL_FOOTER_AUTO . "</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $siteEmail . "\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
}