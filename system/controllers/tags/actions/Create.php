<?php

namespace tags\actions;

/**
* Действие создания нового тега в административной панели
* @package tags\actions
*/
class Create extends TagAction {
    
    /**
    * Метод выполнения создания тега
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_TAGS, ADMIN_URL . '/tags');
        $this->addBreadcrumb(LANG_ACTION_TAGS_CREATE_BREADCRUMB_CREATE);
        
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
                return;
            }
            
            $this->renderCreateForm();
            
        } catch (\Exception $e) {
            $this->handleError($e);
        }
    }
    
    /**
    * Обрабатывает POST-запрос на создание тега
    * @return void
    * @throws \Exception При ошибках валидации или загрузки
    */
    private function handlePostRequest() {

        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_EMPTY_NAME);
        }
        
        $existingTags = $this->tagModel->searchByName($name, 1);
        if (!empty($existingTags)) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_NAME_EXISTS);
        }
        
        $slug = $this->tagModel->createSlugFromName($name);
        
        $data = [
            'name' => $name,
            'slug' => $slug
        ];
        
        if (!empty($_FILES['image']['tmp_name'])) {
            $imageName = $this->handleImageUpload();
            $data['image'] = $imageName;
        }
        
        $this->tagModel->create($data);
        
        \Notification::success(LANG_ACTION_TAGS_CREATE_SUCCESS);
        $this->redirect(ADMIN_URL . '/tags');
    }
    
    /**
    * Отображает форму создания тега
    * @return void
    */
    private function renderCreateForm() {
        $this->render('admin/tags/form', [
            'pageTitle' => LANG_ACTION_TAGS_CREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при создании тега
    * @param \Exception $e Исключение
    * @return void
    */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        $this->render('admin/tags/form', [
            'pageTitle' => LANG_ACTION_TAGS_CREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает загрузку изображения для тега
    * @return string Имя загруженного файла
    * @throws \Exception При ошибках загрузки
    */
    private function handleImageUpload() {
        $uploadDir = UPLOADS_PATH . '/tags/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['image'];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_INVALID_TYPE);
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_FILE_TOO_LARGE);
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception(LANG_ACTION_TAGS_CREATE_ERROR_UPLOAD);
        }
        
        return $fileName;
    }
    
    /**
    * Генерирует URL-адрес (slug) из строки для имени файла
    * @param string $string Исходная строка
    * @return string Очищенная строка для использования в имени файла
    */
    private function generateSlug($string) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        
        $string = strtr(mb_strtolower($string), $converter);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
}