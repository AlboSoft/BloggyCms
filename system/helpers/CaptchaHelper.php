<?php
/**
* Помощник для работы с капчей и защитой от спама
* @package Helpers
*/
class CaptchaHelper {
    
    /**
    * Типы капчи
    */
    const TYPE_MATH = 'math';
    const TYPE_TEXT = 'text';
    const TYPE_IMAGE = 'image';
    
    /**
    * Время жизни капчи (в секундах)
    */
    const CAPTCHA_LIFETIME = 300;
    
    /**
    * Максимум попыток ввода капчи
    */
    const MAX_ATTEMPTS = 3;
    
    /**
    * Генерация капчи и сохранение в сессию
    * @param string $type Тип капчи
    * @param array $settings Настройки формы
    * @return array Данные капчи для отображения
    */
    public static function generate($type = self::TYPE_MATH, $settings = []) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $captchaId = bin2hex(random_bytes(16));
        $captchaData = [];
        
        switch ($type) {
            case self::TYPE_MATH:
                $captchaData = self::generateMathCaptcha($settings);
                break;
            case self::TYPE_TEXT:
                $captchaData = self::generateTextCaptcha($settings);
                break;
            case self::TYPE_IMAGE:
                $captchaData = self::generateImageCaptcha($settings);
                break;
        }
        
        if (!isset($_SESSION['captcha'])) {
            $_SESSION['captcha'] = [];
        }
        
        $_SESSION['captcha'][$captchaId] = [
            'answer' => $captchaData['answer'],
            'created_at' => time(),
            'attempts' => 0,
            'type' => $type
        ];
        
        self::cleanupOldCaptchas();
        
        return [
            'id' => $captchaId,
            'question' => $captchaData['question'],
            'image' => $captchaData['image'] ?? null,
            'type' => $type
        ];
    }
    
    /**
    * Проверка ответа капчи 
    * @param string $captchaId ID капчи
    * @param string $answer Ответ пользователя
    * @return bool true если верно
    */
    public static function verify($captchaId, $answer) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['captcha'][$captchaId])) {
            return false;
        }
        
        $captcha = $_SESSION['captcha'][$captchaId];
        
        if (time() - $captcha['created_at'] > self::CAPTCHA_LIFETIME) {
            unset($_SESSION['captcha'][$captchaId]);
            return false;
        }
        
        if ($captcha['attempts'] >= self::MAX_ATTEMPTS) {
            unset($_SESSION['captcha'][$captchaId]);
            return false;
        }
        
        $_SESSION['captcha'][$captchaId]['attempts']++;
        
        $isCorrect = strtolower(trim($answer)) === strtolower(trim($captcha['answer']));
        
        if ($isCorrect) {
            unset($_SESSION['captcha'][$captchaId]);
        }
        
        return $isCorrect;
    }
    
    /**
    * Генерация математической капчи
    */
    private static function generateMathCaptcha($settings) {
        $operations = ['+', '-', '*'];
        $op = $operations[array_rand($operations)];
        
        switch ($op) {
            case '*':
                $a = rand(2, 9);
                $b = rand(2, 9);
                break;
            case '-':
                $a = rand(10, 50);
                $b = rand(1, $a - 1);
                break;
            case '+':
            default:
                $a = rand(10, 50);
                $b = rand(10, 50);
                break;
        }
        
        $question = sprintf(LANG_HELPER_CAPTCHA_MATH_QUESTION, $a, $op, $b);
        $answer = eval("return {$a} {$op} {$b};");
        
        return [
            'question' => $question,
            'answer' => (string)$answer
        ];
    }
    
    /**
    * Генерация текстовой капчи
    */
    private static function generateTextCaptcha($settings) {
        $questions = [
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_DOG, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_DOG],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_SKY, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_SKY],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_LEGS, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_LEGS],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_WRITE, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_WRITE],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_JANUARY, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_FEBRUARY],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_WEEK_DAYS, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_WEEK_DAYS],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_MORNING_DRINK, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_MORNING_DRINK],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_FISH_HABITAT, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_FISH_HABITAT],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_NIGHT_LIGHT, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_NIGHT_LIGHT],
            ['question' => LANG_HELPER_CAPTCHA_TEXT_QUESTION_TWO_PLUS_TWO, 'answer' => LANG_HELPER_CAPTCHA_TEXT_ANSWER_TWO_PLUS_TWO]
        ];
        
        if (!empty($settings['captcha_question'])) {
            $customQuestion = $settings['captcha_question'];
            $customAnswer = $settings['captcha_answer'] ?? '';
            if (!empty($customAnswer)) {
                return [
                    'question' => $customQuestion,
                    'answer' => $customAnswer
                ];
            }
        }
        
        $item = $questions[array_rand($questions)];
        
        return [
            'question' => $item['question'],
            'answer' => $item['answer']
        ];
    }
    
    /**
    * Генерация изображений капчи
    */
    private static function generateImageCaptcha($settings) {
        if (!extension_loaded('gd')) {
            return self::generateMathCaptcha($settings);
        }
        
        $length = rand(4, 6);
        $chars = 'ABCDEFGHKMNPRSTUVWXYZ23456789';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        
        $width = 200;
        $height = 60;
        $image = imagecreatetruecolor($width, $height);
        
        $bgColor = imagecolorallocate($image, rand(240, 255), rand(240, 255), rand(240, 255));
        imagefill($image, 0, 0, $bgColor);
        
        for ($i = 0; $i < 100; $i++) {
            $noiseColor = imagecolorallocate($image, rand(200, 230), rand(200, 230), rand(200, 230));
            imagesetpixel($image, rand(0, $width), rand(0, $height), $noiseColor);
        }
        
        $font = 5;
        $textColor = imagecolorallocate($image, rand(50, 150), rand(50, 150), rand(50, 150));
        $x = ($width - strlen($code) * imagefontwidth($font)) / 2;
        $y = ($height - imagefontheight($font)) / 2;
        imagestring($image, $font, $x, $y, $code, $textColor);
        
        for ($i = 0; $i < 5; $i++) {
            $lineColor = imagecolorallocate($image, rand(150, 200), rand(150, 200), rand(150, 200));
            imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $lineColor);
        }
        
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        
        return [
            'question' => LANG_HELPER_CAPTCHA_IMAGE_QUESTION,
            'answer' => strtolower($code),
            'image' => 'data:image/png;base64,' . base64_encode($imageData)
        ];
    }
    
    /**
    * Проверка honeypot поля
    */
    public static function verifyHoneypot($data) {
        $honeypotFields = ['website', 'phone_extra', 'company', 'honeypot'];
        
        foreach ($honeypotFields as $field) {
            if (!empty($data[$field])) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
    * Проверка rate limiting
    */
    public static function checkRateLimit($formId, $ip, $settings) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $key = 'rate_limit_' . md5($formId . '_' . $ip);
        $now = time();
        
        $data = $_SESSION[$key] ?? ['count' => 0, 'first_attempt' => $now];
        
        if ($now - $data['first_attempt'] > 3600) {
            $data = ['count' => 0, 'first_attempt' => $now];
        }
        
        $data['count']++;
        $_SESSION[$key] = $data;
        
        $maxPerHour = intval($settings['max_submissions_per_ip'] ?? 10);
        
        if ($data['count'] > $maxPerHour) {
            return false;
        }
        
        return true;
    }
    
    /**
    * Очистка старых капч из сессии
    */
    private static function cleanupOldCaptchas() {
        if (!isset($_SESSION['captcha'])) {
            return;
        }
        
        foreach ($_SESSION['captcha'] as $id => $captcha) {
            if (time() - $captcha['created_at'] > self::CAPTCHA_LIFETIME) {
                unset($_SESSION['captcha'][$id]);
            }
        }
        
        if (empty($_SESSION['captcha'])) {
            unset($_SESSION['captcha']);
        }
    }
    
    /**
    * Рендеринг HTML капчи
    */
    public static function render($captchaData, $settings = []) {
        ob_start();
        ?>
        <div class="captcha-container mb-3">
            <input type="hidden" name="captcha_id" value="<?= html($captchaData['id']) ?>">
            
            <?php if ($captchaData['type'] === 'image' && !empty($captchaData['image'])): ?>
                <div class="captcha-image mb-2">
                    <img src="<?= $captchaData['image'] ?>" alt="Captcha" style="max-width: 200px; border: 1px solid #ddd; border-radius: 4px;">
                    <div class="mt-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="this.closest('.captcha-container').querySelector('img').src='<?= $captchaData['image'] ?>' + '&refresh=' + Date.now()">
                            <i class="bi bi-arrow-clockwise"></i> <?php echo LANG_HELPER_CAPTCHA_REFRESH_BTN; ?>
                        </button>
                    </div>
                </div>
            <?php else: ?>
                <div class="captcha-question mb-2">
                    <label class="form-label">
                        <i class="bi bi-shield-check me-1"></i>
                        <?= html($captchaData['question']) ?>
                        <span class="text-danger">*</span>
                    </label>
                </div>
            <?php endif; ?>
            
            <?php if ($captchaData['type'] !== 'image' || !empty($captchaData['image'])): ?>
                <input type="text"
                    name="captcha_answer"
                    class="form-control"
                    placeholder="<?php echo LANG_HELPER_CAPTCHA_PLACEHOLDER; ?>"
                    autocomplete="off"
                    required>
            <?php endif; ?>
            
            <div class="form-text small text-muted mt-1">
                <i class="bi bi-info-circle me-1"></i>
                <?php echo LANG_HELPER_CAPTCHA_INFO_TEXT; ?>
            </div>
        </div>

        <div style="position: absolute; left: -9999px;" aria-hidden="true">
            <input type="text" name="website" tabindex="-1" autocomplete="off">
            <input type="text" name="honeypot" tabindex="-1" autocomplete="off">
        </div>
        <?php
        return ob_get_clean();
    }
}