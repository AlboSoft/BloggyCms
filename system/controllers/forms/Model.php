<?php

/**
* Модель форм
* @package models
*/
class FormModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getBySlug',
        'getById',
        'saveSubmission',
        'getAvailableTemplates',
        'getCurrentTheme',
        'getAllActive',
        'validateFormStructure'
    ];
    
    private $db;
    
    /**
    * Конструктор модели форм
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
    * Создание новой формы
    * @param array $data Массив данных формы
    * @return int ID созданной формы
    */
    public function create($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (isset($data['structure']) && is_array($data['structure'])) {
            $data['structure'] = json_encode($data['structure'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['notifications']) && is_array($data['notifications'])) {
            $data['notifications'] = json_encode($data['notifications'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['actions']) && is_array($data['actions'])) {
            $data['actions'] = json_encode($data['actions'], JSON_UNESCAPED_UNICODE);
        }
        
        $this->db->insert('forms', $data);
        return $this->db->lastInsertId();
    }
    
    /**
    * Обновление существующей формы
    * @param int $id ID обновляемой формы
    * @param array $data Массив данных для обновления
    * @return bool Результат выполнения операции
    */
    public function update($id, $data) {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (isset($data['structure']) && is_array($data['structure'])) {
            $data['structure'] = json_encode($data['structure'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['notifications']) && is_array($data['notifications'])) {
            $data['notifications'] = json_encode($data['notifications'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['actions']) && is_array($data['actions'])) {
            $data['actions'] = json_encode($data['actions'], JSON_UNESCAPED_UNICODE);
        }
        
        return $this->db->update('forms', $data, ['id' => $id]) > 0;
    }
    
    /**
    * Удаление формы
    * @param int $id ID удаляемой формы
    * @return bool Результат выполнения операции
    */
    public function delete($id) {
        return $this->db->delete('forms', ['id' => $id]) > 0;
    }
    
    /**
    * Получение формы по ID
    * @param int $id ID формы
    * @return array|null Данные формы или null если не найдена
    */
    public function getById($id) {
        $form = $this->db->fetch(
            "SELECT * FROM forms WHERE id = ?", 
            [(int)$id]
        );
        
        if ($form) {
            $this->decodeFormData($form);
        }
        
        return $form;
    }
    
    /**
    * Получение формы по слагу
    * @param string $slug Слаг формы
    * @return array|null Данные формы или null если не найдена
    */
    public function getBySlug($slug) {
        $form = $this->db->fetch(
            "SELECT * FROM forms WHERE slug = ? AND status = 'active'", 
            [$slug]
        );
        
        if ($form) {
            $this->decodeFormData($form);
        }
        
        return $form;
    }
    
    /**
    * Получение всех форм
    * @return array Массив всех форм
    */
    public function getAll() {
        $forms = $this->db->fetchAll(
            "SELECT * FROM forms ORDER BY created_at DESC"
        );
        
        foreach ($forms as &$form) {
            if (isset($form['structure'])) {
                $form['structure'] = json_decode($form['structure'], true) ?: [];
            }
            if (isset($form['settings'])) {
                $form['settings'] = json_decode($form['settings'], true) ?: [];
            }
            if (isset($form['notifications'])) {
                $form['notifications'] = json_decode($form['notifications'], true) ?: [];
            }
            if (isset($form['actions'])) {
                $form['actions'] = json_decode($form['actions'], true) ?: [];
            }
        }
        
        return $forms;
    }
    
    /**
    * Получение активных форм
    * @return array Массив активных форм
    */
    public function getAllActive() {
        $forms = $this->db->fetchAll(
            "SELECT * FROM forms WHERE status = 'active' ORDER BY name ASC"
        );
        
        foreach ($forms as &$form) {
            $this->decodeFormData($form);
        }
        
        return $forms;
    }
    
    /**
    * Декодирование данных формы из JSON
    * @param array &$form Ссылка на массив формы
    */
    private function decodeFormData(&$form) {
        if (isset($form['structure'])) {
            $form['structure'] = json_decode($form['structure'], true) ?: [];
        }
        if (isset($form['settings'])) {
            $form['settings'] = json_decode($form['settings'], true) ?: [];
        }
        if (isset($form['notifications'])) {
            $form['notifications'] = json_decode($form['notifications'], true) ?: [];
        }
        if (isset($form['actions'])) {
            $form['actions'] = json_decode($form['actions'], true) ?: [];
        }
    }
    
    /**
    * Валидация структуры формы
    * @param array $structure Структура формы для проверки
    * @return array Результат валидации [success, errors]
    */
    public function validateFormStructure($structure) {
        if (!is_array($structure)) {
            return [false, ['Структура формы должна быть массивом']];
        }
        
        $errors = [];
        $fieldNames = [];
        $hasSubmit = false;
        
        foreach ($structure as $index => $field) {
            if (empty($field['type'])) {
                $errors[] = "Поле #{$index}: не указан тип поля";
            }
            
            if (empty($field['label']) && $field['type'] !== 'hidden') {
                $errors[] = "Поле #{$index}: не указан заголовок поля";
            }
            
            if (empty($field['name']) && $field['type'] !== 'submit') {
                $errors[] = "Поле #{$index}: не указано имя поля (атрибут name)";
            }
            
            if (!empty($field['name']) && $field['type'] !== 'submit') {
                if (in_array($field['name'], $fieldNames)) {
                    $errors[] = "Поле #{$index}: имя '{$field['name']}' уже используется другим полем";
                } else {
                    $fieldNames[] = $field['name'];
                }
            }
            
            $validTypes = ['text', 'textarea', 'email', 'tel', 'number', 'date', 
                          'select', 'checkbox', 'radio', 'file', 'password', 
                          'hidden', 'submit'];
            if (!in_array($field['type'], $validTypes)) {
                $errors[] = "Поле #{$index}: недопустимый тип поля '{$field['type']}'";
            }
            
            if ($field['type'] === 'submit') {
                $hasSubmit = true;
            }
        }
        
        if (!$hasSubmit) {
            $errors[] = "Форма должна содержать кнопку отправки (тип 'submit')";
        }
        
        return [empty($errors), $errors];
    }
    
    /**
    * Создание слага формы
    * @param string $name Имя формы
    * @return string Уникальный слаг
    */
    public function createSlug($name) {
        $slug = $this->transliterate($name);
        $slug = preg_replace('/[^a-z0-9_-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        $slug = strtolower($slug);
        $counter = 1;
        $originalSlug = $slug;
        
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
    * Транслитерация текста
    */
    private function transliterate($text) {
        $cyr = [
            'а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п',
            'р','с','т','у','ф','х','ц','ч','ш','щ','ъ','ы','ь','э','ю','я',
            'А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П',
            'Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','Ы','Ь','Э','Ю','Я'
        ];
        
        $lat = [
            'a','b','v','g','d','e','yo','zh','z','i','y','k','l','m','n','o','p',
            'r','s','t','u','f','h','ts','ch','sh','sht','a','i','y','e','yu','ya',
            'A','B','V','G','D','E','Yo','Zh','Z','I','Y','K','L','M','N','O','P',
            'R','S','T','U','F','H','Ts','Ch','Sh','Sht','A','I','Y','E','Yu','Ya'
        ];
        
        return str_replace($cyr, $lat, $text);
    }
    
    /**
    * Проверка существования слага
    */
    private function slugExists($slug) {
        $existing = $this->db->fetch(
            "SELECT id FROM forms WHERE slug = ?",
            [$slug]
        );
        return !empty($existing);
    }
    
    /**
    * Сохранение отправки формы
    * @param int $formId ID формы
    * @param array $data Данные формы
    * @param array $files Загруженные файлы
    * @return int ID созданной отправки
    */
    public function saveSubmission($formId, $data, $files = []) {
        $submissionData = [
            'form_id' => $formId,
            'data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('form_submissions', $submissionData);
        $submissionId = $this->db->lastInsertId();
        
        if (!empty($files) && $submissionId) {
            $this->saveSubmissionFiles($submissionId, $files);
        }
        
        return $submissionId;
    }
    
    /**
    * Сохранение файлов отправки (с поддержкой multiple)
    */
        private function saveSubmissionFiles($submissionId, $files) {
        $uploadPath = ROOT_PATH . '/storage/uploads/forms';
        
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }
        
        $monthDir = date('Y-m');
        $fullPath = $uploadPath . '/' . $monthDir;
        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0755, true);
        }

        $this->protectUploadDirectory($uploadPath);

        foreach ($files as $fieldName => $fileData) {
            if (is_array($fileData['name']) && isset($fileData['name'][0])) {
                $count = count($fileData['name']);
                for ($i = 0; $i < $count; $i++) {
                    if ($fileData['error'][$i] !== UPLOAD_ERR_OK) continue;
                    
                    $file = [
                        'name' => $fileData['name'][$i],
                        'tmp_name' => $fileData['tmp_name'][$i],
                        'size' => $fileData['size'][$i],
                        'type' => $fileData['type'][$i],
                        'error' => $fileData['error'][$i]
                    ];
                    
                    if ($this->validateUploadedFile($file, $fieldName)) {
                        $this->saveSingleFile($submissionId, $fieldName, $file, $fullPath, $monthDir);
                    }
                }
            } else {
                if ($fileData['error'] !== UPLOAD_ERR_OK) continue;
                
                if ($this->validateUploadedFile($fileData, $fieldName)) {
                    $this->saveSingleFile($submissionId, $fieldName, $fileData, $fullPath, $monthDir);
                }
            }
        }
    }

    /**
    * Защита директории загрузок от выполнения PHP
    */
    private function protectUploadDirectory($uploadPath) {
        $htaccessPath = $uploadPath . '/.htaccess';
        if (!file_exists($htaccessPath)) {
            $htaccessContent = "# Запрет выполнения PHP в директории загрузок\n";
            $htaccessContent .= "<FilesMatch \"\.(php|phtml|php[0-9]|phar|cgi|pl|sh|shtml|html|htm)$\">\n";
            $htaccessContent .= "    Order Deny,Allow\n";
            $htaccessContent .= "    Deny from all\n";
            $htaccessContent .= "</FilesMatch>\n\n";
            $htaccessContent .= "# Запрет доступа к скрытым файлам\n";
            $htaccessContent .= "RedirectMatch 403 /\..*$\n";
            
            file_put_contents($htaccessPath, $htaccessContent);
        }
    }

    /**
    * Валидация загружаемого файла
    * @param array $file Данные файла
    * @param string $fieldName Имя поля
    * @return bool true если файл валиден
    * @throws Exception Если файл не прошел валидацию
    */
    private function validateUploadedFile($file, $fieldName) {
        $maxFileSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxFileSize) {
            throw new Exception("Файл '{$file['name']}' превышает максимальный размер (10MB)");
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $realMimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        } else {
            $realMimeType = mime_content_type($file['tmp_name']);
        }

        $allowedMimeTypes = [
            'image/jpeg',
            'image/png', 
            'image/gif',
            'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain',
            'application/zip',
            'application/x-rar-compressed'
        ];

        if (!in_array($realMimeType, $allowedMimeTypes)) {
            throw new Exception("Недопустимый тип файла '{$file['name']}'. Разрешены: " . implode(', ', $allowedMimeTypes));
        }

        $allowedExtensions = [
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'pdf', 'doc', 'docx', 'xls', 'xlsx',
            'txt', 'zip', 'rar'
        ];
        
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (empty($extension)) {
            throw new Exception("Файл '{$file['name']}' не имеет расширения");
        }

        if (!in_array($extension, $allowedExtensions)) {
            throw new Exception("Недопустимое расширение файла '{$file['name']}'. Разрешены: " . implode(', ', $allowedExtensions));
        }

        $mimeToExtensionMap = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/gif' => ['gif'],
            'image/webp' => ['webp'],
            'application/pdf' => ['pdf'],
            'application/msword' => ['doc'],
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
            'application/vnd.ms-excel' => ['xls'],
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
            'text/plain' => ['txt'],
            'application/zip' => ['zip'],
            'application/x-rar-compressed' => ['rar']
        ];

        if (isset($mimeToExtensionMap[$realMimeType])) {
            if (!in_array($extension, $mimeToExtensionMap[$realMimeType])) {
                throw new Exception("Расширение файла '{$extension}' не соответствует типу '{$realMimeType}'");
            }
        }

        if (preg_match('/\.[a-zA-Z0-9]+\.[a-zA-Z0-9]+$/', $file['name'])) {
            throw new Exception("Файл '{$file['name']}' имеет подозрительное двойное расширение");
        }

        $dangerousExtensions = ['php', 'phtml', 'php3', 'php4', 'php5', 'phar', 'cgi', 'pl', 'sh'];
        if (in_array($extension, $dangerousExtensions)) {
            throw new Exception("Загрузка PHP-файлов запрещена");
        }

        if (strpos($realMimeType, 'image/') === 0) {
            $imageInfo = @getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                throw new Exception("Файл '{$file['name']}' не является корректным изображением");
            }
            
            $content = file_get_contents($file['tmp_name']);
            if (strpos($content, '<?php') !== false || strpos($content, '<?=') !== false) {
                throw new Exception("Файл '{$file['name']}' содержит подозрительный код");
            }
        }

        $content = file_get_contents($file['tmp_name']);
        $dangerousPatterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<\?xml/i',
            '/<script/i',
            '/eval\s*\(/i',
            '/base64_decode\s*\(/i',
            '/system\s*\(/i',
            '/exec\s*\(/i',
            '/passthru\s*\(/i',
            '/shell_exec\s*\(/i',
            '/popen\s*\(/i',
            '/proc_open\s*\(/i',
            '/assert\s*\(/i',
            '/create_function\s*\(/i',
            '/GIF89a.*<\?php/i'
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $content)) {
                throw new Exception("Файл '{$file['name']}' содержит потенциально опасный код");
            }
        }

        return true;
    }

    /**
    * Вспомогательный метод для сохранения одного файла
    */
    private function saveSingleFile($submissionId, $fieldName, $file, $fullPath, $monthDir) {
        $safeName = $this->generateSafeFileName($file['name']);
        $fileName = time() . '_' . uniqid() . '_' . $safeName;
        $filePath = $fullPath . '/' . $fileName;
        
        if (file_exists($filePath)) {
            $fileName = time() . '_' . uniqid() . '_' . $safeName;
            $filePath = $fullPath . '/' . $fileName;
        }
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            chmod($filePath, 0644);
            
            $fileData = [
                'submission_id' => $submissionId,
                'field_name' => $fieldName,
                'file_name' => $file['name'],
                'file_path' => 'storage/uploads/forms/' . $monthDir . '/' . $fileName,
                'file_size' => $file['size'],
                'mime_type' => $file['type'],
                'real_mime_type' => $this->getRealMimeType($filePath),
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('form_files', $fileData);
        }
    }

    /**
    * Генерация безопасного имени файла
    */
    private function generateSafeFileName($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $name = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        $name = trim($name, '_');
        
        if (empty($name)) {
            $name = 'file';
        }
        
        $name = mb_substr($name, 0, 50);
        
        return $name . '.' . $extension;
    }

    /**
    * Получение реального MIME-типа файла
    */
    private function getRealMimeType($filePath) {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        }
        return mime_content_type($filePath);
    }
    
    /**
    * Очистка имени файла
    */
    private function sanitizeFileName($filename) {
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $filename);
        return preg_replace('/_+/', '_', $filename);
    }
    
    /**
    * Получение отправок формы
    * @param int $formId ID формы
    * @param int $limit Лимит записей
    * @param int $offset Смещение
    * @return array Массив отправок
    */
    public function getSubmissions($formId, $limit = 50, $offset = 0) {
        $submissions = $this->db->fetchAll(
            "SELECT * FROM form_submissions
            WHERE form_id = ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?",
            [$formId, $limit, $offset]
        );
        
        foreach ($submissions as &$submission) {
            $submission['data'] = json_decode($submission['data'], true) ?: [];
            
            $submission['files'] = $this->getSubmissionFiles($submission['id']);
            
            if (!empty($submission['files'])) {
                foreach ($submission['files'] as $file) {
                    $submission['data']['_files'][$file['field_name']] = [
                        'name' => $file['file_name'],
                        'path' => $file['file_path'],
                        'size' => $file['file_size']
                    ];
                }
            }
        }
        return $submissions;
    }
    
    /**
    * Получение файлов отправки
    */
    private function getSubmissionFiles($submissionId) {
        return $this->db->fetchAll(
            "SELECT * FROM form_files WHERE submission_id = ?",
            [$submissionId]
        );
    }
    
    /**
    * Удаление отправки
    */
    public function deleteSubmission($submissionId) {
        $files = $this->db->fetchAll(
            "SELECT * FROM form_files WHERE submission_id = ?",
            [$submissionId]
        );
        
        foreach ($files as $file) {
            $filePath = ROOT_PATH . '/' . $file['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
        
        $this->db->delete('form_files', ['submission_id' => $submissionId]);
        
        $result = $this->db->delete('form_submissions', ['id' => $submissionId]);
        
        if (is_object($result) && method_exists($result, 'rowCount')) {
            return $result->rowCount() > 0;
        }
        
        return (bool)$result;
    }
    
    /**
    * Обновление статуса отправки
    */
    public function updateSubmissionStatus($submissionId, $status) {
        return $this->db->update('form_submissions', 
            ['status' => $status], 
            ['id' => $submissionId]
        ) > 0;
    }
    
    /**
    * Получение статистики по формам
    * @return array Статистика
    */
    public function getStatistics() {
        $stats = [];
        
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM forms");
        $stats['total_forms'] = $result['count'] ?? 0;
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM forms WHERE status = 'active'");
        $stats['active_forms'] = $result['count'] ?? 0;
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM form_submissions");
        $stats['total_submissions'] = $result['count'] ?? 0;
        
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM form_submissions WHERE DATE(created_at) = CURDATE()"
        );
        $stats['today_submissions'] = $result['count'] ?? 0;
        
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM form_submissions WHERE status = 'new'"
        );
        $stats['unread_submissions'] = $result['count'] ?? 0;
        
        return $stats;
    }
    
    /**
    * Экспорт отправок в CSV
    * @param int $formId ID формы
    * @return string CSV данные
    */
    public function exportSubmissionsToCSV($formId) {
        $submissions = $this->getSubmissions($formId, 1000);
        if (empty($submissions)) return '';
        
        $form = $this->getById($formId);
        $fields = $form['structure'] ?? [];
        
        $headers = ['ID', 'Дата', 'Статус', 'IP адрес'];
        foreach ($fields as $field) {
            if ($field['type'] !== 'submit' && $field['type'] !== 'hidden') {
                $headers[] = $field['label'] ?? $field['name'];
            }
        }
        
        $delimiter = ';';
        
        $output = fopen('php://temp', 'r+');
        
        fwrite($output, "\xEF\xBB\xBF");
        fputcsv($output, $headers, $delimiter);
        
        foreach ($submissions as $submission) {
            $row = [
                $submission['id'],
                $submission['created_at'],
                $this->getStatusText($submission['status']),
                $submission['ip_address']
            ];
            
            foreach ($fields as $field) {
                if ($field['type'] !== 'submit' && $field['type'] !== 'hidden') {
                    $fieldName = $field['name'] ?? '';
                    $value = $submission['data'][$fieldName] ?? '';
                    
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    
                    $value = mb_convert_encoding($value, 'UTF-8', 'auto');
                    
                    $row[] = $value;
                }
            }
            
            fputcsv($output, $row, $delimiter);
        }
        
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        return $csv;
    }

    /**
    * Получает текст статуса
    */
    private function getStatusText($status) {
        $statuses = [
            'new' => 'Новый',
            'read' => 'Прочитан',
            'processed' => 'Обработан',
            'spam' => 'Спам'
        ];
        
        return $statuses[$status] ?? $status;
    }
    
    /**
    * Получение количества отправок по статусам
    */
    public function getSubmissionsCountByStatus($formId) {
        $result = $this->db->fetchAll(
            "SELECT status, COUNT(*) as count 
             FROM form_submissions 
             WHERE form_id = ? 
             GROUP BY status",
            [$formId]
        );
        
        $counts = [
            'new' => 0,
            'read' => 0,
            'processed' => 0,
            'spam' => 0,
            'total' => 0
        ];
        
        foreach ($result as $row) {
            if (isset($counts[$row['status']])) {
                $counts[$row['status']] = $row['count'];
                $counts['total'] += $row['count'];
            }
        }
        
        return $counts;
    }

    /**
    * Получение количества отправок формы
    * @param int $formId ID формы
    * @return int Количество отправок
    */
    public function getSubmissionsCount($formId) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM form_submissions WHERE form_id = ?",
            [$formId]
        );
        
        return $result['count'] ?? 0;
    }

    
    /**
    * Получение последних отправок
    */
    public function getRecentSubmissions($limit = 10) {
        $submissions = $this->db->fetchAll("
            SELECT fs.*, f.name as form_name 
            FROM form_submissions fs
            JOIN forms f ON fs.form_id = f.id
            ORDER BY fs.created_at DESC 
            LIMIT ?
        ", [$limit]);
        
        foreach ($submissions as &$submission) {
            $submission['data'] = json_decode($submission['data'], true) ?: [];
        }
        
        return $submissions;
    }
    
    /**
    * Получение статистики по дням
    */
    public function getDailyStatistics($days = 30) {
        $result = $this->db->fetchAll("
            SELECT 
                DATE(created_at) as date,
                COUNT(*) as count
            FROM form_submissions 
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            GROUP BY DATE(created_at)
            ORDER BY date ASC
        ", [$days]);
        
        return $result;
    }
    
    /**
    * Поиск отправок
    */
    public function searchSubmissions($formId, $searchTerm, $status = null) {
        $query = "
            SELECT * FROM form_submissions 
            WHERE form_id = ? 
            AND (data LIKE ? OR ip_address LIKE ?)
        ";
        
        $params = [$formId, "%$searchTerm%", "%$searchTerm%"];
        
        if ($status) {
            $query .= " AND status = ?";
            $params[] = $status;
        }
        
        $query .= " ORDER BY created_at DESC LIMIT 50";
        
        $submissions = $this->db->fetchAll($query, $params);
        
        foreach ($submissions as &$submission) {
            $submission['data'] = json_decode($submission['data'], true) ?: [];
        }
        
        return $submissions;
    }

    /**
    * Получает текущую тему
    */
    public static function getCurrentTheme() {
        if (defined('DEFAULT_TEMPLATE')) {
            return DEFAULT_TEMPLATE;
        }
        
        return 'default';
    }

    /**
    * Получение доступных шаблонов форм
    * @return array Массив шаблонов [id => название]
    */
    public function getAvailableTemplates() {
        $templates = [];

        $templates['default'] = 'Стандартный шаблон';
        
        $currentTheme = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        $templatesPath = ROOT_PATH . '/templates/' . $currentTheme . '/front/assets/forms/';
        
        if (is_dir($templatesPath)) {
            $files = scandir($templatesPath);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') continue;
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $templateName = pathinfo($file, PATHINFO_FILENAME);
                    
                    if ($templateName === 'default') continue;
                    
                    $templateInfo = $this->getTemplateInfo($templatesPath . $file);
                    $templates[$templateName] = $templateInfo['name'] ?? ucfirst($templateName);
                }
            }
        }
        
        return $templates;
    }
    
    /**
    * Получение информации о шаблоне
    */
    private function getTemplateInfo($filePath) {
        $info = [
            'name' => basename($filePath, '.php'),
            'description' => '',
            'version' => '1.0.0'
        ];
        
        if (!file_exists($filePath)) {
            return $info;
        }
        
        $content = file_get_contents($filePath);
        
        if (preg_match('/\/\*\*(.*?)\*\//s', $content, $matches)) {
            $comment = $matches[1];
            
            if (preg_match('/@name\s+(.*)/', $comment, $nameMatch)) {
                $info['name'] = trim($nameMatch[1]);
            }
            
            if (preg_match('/@description\s+(.*)/', $comment, $descMatch)) {
                $info['description'] = trim($descMatch[1]);
            }
        }
        
        return $info;
    }

    /**
    * Получение количества отправок за сегодня с IP
    * @param int $formId ID формы
    * @param string $ip IP адрес
    * @return int Количество отправок
    */
    public function getSubmissionsCountToday($formId, $ip = '') {
        $query = "SELECT COUNT(*) as count FROM form_submissions 
                WHERE form_id = ? 
                AND DATE(created_at) = CURDATE()";
        $params = [$formId];
        
        if (!empty($ip)) {
            $query .= " AND ip_address = ?";
            $params[] = $ip;
        }
        
        $result = $this->db->fetch($query, $params);
        return $result['count'] ?? 0;
    }

    /**
    * Получение количества отправок с IP
    * @param int $formId ID формы
    * @param string $ip IP адрес
    * @return int Количество отправок
    */
    public function getSubmissionsCountByIp($formId, $ip = '') {
        if (empty($ip)) {
            return 0;
        }
        
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM form_submissions 
            WHERE form_id = ? AND ip_address = ?",
            [$formId, $ip]
        );
        return $result['count'] ?? 0;
    }

}