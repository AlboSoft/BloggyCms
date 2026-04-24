<?php

namespace users\actions\achievements;

/**
* Действие создания нового достижения (ачивки) в административной панели
* @package users\actions\achievements
*/
class AdminAchievementCreate extends AdminAchievementAction {
    
    /**
    * Метод выполнения создания ачивки
    * @return void
    */
    public function execute() {
        try {

            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_BREADCRUMB_ACHIEVEMENTS, ADMIN_URL . '/user-achievements');
            $this->addBreadcrumb(LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_BREADCRUMB_CREATE);

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
    * Обрабатывает POST-запрос на создание ачивки
    * @return void
    * @throws \Exception При ошибках валидации
    */
    private function handlePostRequest() {
        if (empty($_POST['name'])) {
            throw new \Exception(LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_ERROR_NAME_REQUIRED);
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
        
        $imageName = $this->handleImageUpload();
        if ($imageName) {
            $achievementData['image'] = $imageName;
        }
        
        $achievementId = $this->userModel->createAchievement($achievementData);
        
        \Notification::success(LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_SUCCESS);
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
    * Обрабатывает загрузку изображения для ачивки 
    * @return string|null Имя загруженного файла или null
    */
    private function handleImageUpload() {
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
            return $fileName;
        }
        
        return null;
    }
    
    /**
    * Отображает форму создания ачивки
    * @return void
    */
    private function renderCreateForm() {
        $this->render('admin/user-achievements/create', [
            'pageTitle' => LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_PAGE_TITLE
        ]);
    }
    
    /**
    * Обрабатывает ошибку при создании ачивки
    * @param \Exception $e Исключение
    * @return void
    */
    private function handleError($e) {
        \Notification::error($e->getMessage());
        
        $this->render('admin/user-achievements/create', [
            'achievement' => $_POST,
            'pageTitle' => LANG_ACTION_USERS_ADMINACHIEVEMENTCREATE_PAGE_TITLE
        ]);
    }
}