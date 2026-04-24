<?php

namespace users\actions\achievements;

/**
* Действие редактирования достижения (ачивки) в административной панели
* @package users\actions\achievements
*/
class AdminAchievementEdit extends AdminAchievementAction {
    
    /**
    * Метод выполнения редактирования ачивки
    * @return void
    */
    public function execute() {
        try {
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_NO_ID);
            }
            
            $achievement = $this->userModel->getAchievementById($id);
            if (!$achievement) {
                throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_NOT_FOUND);
            }
            
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_BREADCRUMB_ACHIEVEMENTS, ADMIN_URL . '/user-achievements');
            $this->addBreadcrumb(sprintf(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_BREADCRUMB_EDIT, $achievement['name']));
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id, $achievement);
                return;
            }
            
            $this->renderEditForm($achievement);
            
        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/user-achievements');
        }
    }
    
    /**
    * Обрабатывает POST-запрос на обновление ачивки
    * @param int $id ID ачивки
    * @param array $achievement Текущие данные ачивки
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest($id, $achievement) {

        if (empty($_POST['name'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_ERROR_NAME_REQUIRED);
        }
        
        $conditions = $this->prepareConditions();
        
        $achievementData = [
            'name' => $_POST['name'],
            'description' => $_POST['description'] ?? '',
            'icon' => $_POST['icon'] ?? 'trophy',
            'icon_color' => $_POST['icon_color'] ?? '#0088cc',
            'type' => $_POST['type'] ?? 'auto',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'priority' => $_POST['priority'] ?? 0,
            'conditions' => $conditions
        ];
        
        $imageName = $this->handleImageUpload($achievement);
        if ($imageName) {
            $achievementData['image'] = $imageName;
        }
        
        if (isset($_POST['remove_image']) && $_POST['remove_image']) {
            $this->handleImageDelete($achievement);
            $achievementData['image'] = null;
        }

        $this->userModel->updateAchievement($id, $achievementData);
        
        \Notification::success(LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_SUCCESS);
        $this->redirect(ADMIN_URL . '/user-achievements');
    }
    
    /**
    * Подготавливает массив условий из POST-данных
    * @return array Массив условий для ачивки
    */
    private function prepareConditions() {
        $conditions = [];
        
        if (!empty($_POST['conditions'])) {
            foreach ($_POST['conditions'] as $condition) {
                if (!empty($condition['type']) && !empty($condition['operator']) && isset($condition['value'])) {
                    $conditions[] = [
                        'type' => $condition['type'],
                        'operator' => $condition['operator'],
                        'value' => $condition['value']
                    ];
                }
            }
        }
        
        return $conditions;
    }
    
    /**
    * Обрабатывает загрузку нового изображения для ачивки 
    * @param array $achievement Текущие данные ачивки
    * @return string|null Имя загруженного файла или null
    */
    private function handleImageUpload($achievement) {
        if (empty($_FILES['image']['tmp_name'])) {
            return null;
        }
        
        $uploadDir = UPLOADS_PATH . '/achievements/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
        $targetPath = $uploadDir . $fileName;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
            if (!empty($achievement['image'])) {
                $oldImage = $uploadDir . $achievement['image'];
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
            }
            return $fileName;
        }
        
        return null;
    }
    
    /**
    * Обрабатывает удаление изображения ачивки
    * @param array $achievement Текущие данные ачивки
    * @return void
    */
    private function handleImageDelete($achievement) {
        if (!empty($achievement['image'])) {
            $uploadDir = UPLOADS_PATH . '/achievements/';
            $oldImage = $uploadDir . $achievement['image'];
            if (file_exists($oldImage)) {
                unlink($oldImage);
            }
        }
    }
    
    /**
    * Отображает форму редактирования ачивки 
    * @param array $achievement Данные ачивки
    * @return void
    */
    private function renderEditForm($achievement) {
        $this->render('admin/user-achievements/edit', [
            'achievement' => $achievement,
            'pageTitle' => LANG_ACTION_USERS_ADMINACHIEVEMENTEDIT_PAGE_TITLE
        ]);
    }
}