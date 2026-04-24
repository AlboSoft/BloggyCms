<?php

namespace tags\actions;

/**
* Действие редактирования тега в административной панели
* @package tags\actions
* @extends TagAction
*/
class Edit extends TagAction {
    
    /**
    * Метод выполнения редактирования тега
    * @return void
    */
    public function execute() {

        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error(LANG_ACTION_TAGS_EDIT_NO_ID);
            $this->redirect(ADMIN_URL . '/tags');
            return;
        }
        
        try {
            $tag = $this->tagModel->getById($id);
            if (!$tag) {
                \Notification::error(LANG_ACTION_TAGS_EDIT_TAG_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/tags');
                return;
            }
            
            $this->addBreadcrumb(LANG_ACTION_TAGS_EDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_TAGS_EDIT_BREADCRUMB_TAGS, ADMIN_URL . '/tags');
            $this->addBreadcrumb(sprintf(LANG_ACTION_TAGS_EDIT_BREADCRUMB_EDIT, $tag['name']));
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $tag);
                return;
            }
            
            $this->renderEditForm($tag);
            
        } catch (\Exception $e) {
            $this->handleError($e, $id);
        }
    }
    
    /**
    * Обрабатывает POST-запрос на обновление тега
    * @param int $id ID тега
    * @param array $tag Текущие данные тега
    * @return void
    * @throws \Exception При ошибках валидации или загрузки
    */
    private function handlePostRequest($id, $tag) {

        $name = trim($_POST['name'] ?? '');
        
        if (empty($name)) {
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_EMPTY_NAME);
        }
        
        $existingTags = $this->tagModel->searchByName($name, 1);
        if (!empty($existingTags) && $existingTags[0]['id'] != $id) {
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_NAME_EXISTS);
        }
        
        $slug = $this->tagModel->createSlugFromName($name);
        
        $data = $this->prepareUpdateData($tag);
        $data['name'] = $name;
        $data['slug'] = $slug;
        
        $this->tagModel->update($id, $data);
        
        \Notification::success(LANG_ACTION_TAGS_EDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/tags');
    }
    
    /**
    * Подготавливает данные для обновления с учетом изображения
    * @param array $tag Текущие данные тега
    * @return array Массив данных для обновления
    * @throws \Exception При ошибке загрузки изображения
    */
    private function prepareUpdateData($tag) {
        $data = [];
        
        if (isset($_POST['remove_image']) && $_POST['remove_image']) {
            if (!empty($tag['image'])) {
                $this->deleteImage($tag['image']);
            }
            $data['image'] = null;
        }
        elseif (!empty($_FILES['image']['tmp_name'])) {
            if (!empty($tag['image'])) {
                $this->deleteImage($tag['image']);
            }
            $imageName = $this->handleImageUpload();
            $data['image'] = $imageName;
        }
        else {
            $data['image'] = $tag['image'];
        }
        
        return $data;
    }
    
    /**
    * Отображает форму редактирования тега 
    * @param array $tag Данные тега
    * @return void
    */
    private function renderEditForm($tag) {
        $this->render('admin/tags/form', [
            'tag' => $tag,
            'pageTitle' => LANG_ACTION_TAGS_EDIT_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при редактировании тега
    * @param \Exception $e Исключение
    * @param int $id ID тега
    * @return void
    */
    private function handleError($e, $id) {
        \Notification::error($e->getMessage());
        
        $tag = $this->tagModel->getById($id);
        $this->render('admin/tags/form', [
            'tag' => $tag,
            'pageTitle' => LANG_ACTION_TAGS_EDIT_PAGE_TITLE
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
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_INVALID_TYPE);
        }
        
        if ($file['size'] > 2 * 1024 * 1024) {
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_FILE_TOO_LARGE);
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception(LANG_ACTION_TAGS_EDIT_ERROR_UPLOAD);
        }
        
        return $fileName;
    }
    
    /**
    * Удаляет изображение тега с сервера
    * @param string $imageName Имя файла изображения
    * @return void
    */
    private function deleteImage($imageName) {
        $imagePath = UPLOADS_PATH . '/tags/' . $imageName;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
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