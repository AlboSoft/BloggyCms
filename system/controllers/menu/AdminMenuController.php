<?php

/**
* Контроллер управления меню в админ-панели
* @package controllers
*/
class AdminMenuController extends Controller {
    
    private $menuModel;
    
    /**
    * Конструктор контроллера меню
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->menuModel = new MenuModel($db);
        
        if (!isset($_SESSION['user_id'])) {
            \Notification::error(LANG_CONTROLLER_ADMINMENU_AUTH_REQUIRED);
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            \Notification::error(LANG_CONTROLLER_ADMINMENU_ACCESS_DENIED);
            $this->redirect(BASE_URL);
            return;
        }
    }
    
    /**
    * Действие: Главная страница управления меню 
    * @return mixed
    */
    public function adminIndexAction() {
        $action = new \menu\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Создание нового меню
    * @return mixed
    */
    public function createAction() {
        $action = new \menu\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование существующего меню
    * @param int $id ID редактируемого меню
    * @return mixed
    */
    public function editAction($id) {
        $action = new \menu\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление меню
    * @param int $id ID удаляемого меню
    * @return mixed
    */
    public function deleteAction($id) {
        $action = new \menu\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Получение структуры меню через AJAX
    * @param int $id ID меню
    * @return mixed JSON-ответ со структурой меню
    */
    public function getStructureAction($id) {
        $action = new \menu\actions\AdminGetStructure($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Предварительный просмотр меню
    * @param int $id ID меню для предпросмотра
    * @return mixed
    */
    public function previewAction($id) {
        $action = new \menu\actions\AdminPreview($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Рендеринг одного пункта меню для формы (рекурсивно)
    * @param array $item Данные пункта меню
    * @param string $index Уникальный индекс пункта в структуре
    * @return string HTML-код пункта меню
    */
    public function renderMenuItem($item, $index) {
        $title = html($item['title'] ?? '');
        $url = html($item['url'] ?? '');
        $class = html($item['class'] ?? '');
        $target = $item['target'] ?? '_self';
        $children = $item['children'] ?? [];
        
        ob_start();
        ?>
        <div class="menu-item card mb-2" data-index="<?= $index ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <h6 class="card-title mb-0"><?php echo LANG_CONTROLLER_ADMINMENU_MENU_ITEM; ?></h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary menu-item-handle">
                            <i class="bi bi-arrows-move"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary toggle-children">
                            <i class="bi bi-list-nested"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger remove-menu-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small"><?php echo LANG_CONTROLLER_ADMINMENU_FIELD_TITLE; ?></label>
                            <input type="text" 
                                class="form-control form-control-sm menu-item-title" 
                                placeholder="<?php echo LANG_CONTROLLER_ADMINMENU_PLACEHOLDER_TITLE; ?>" 
                                maxlength="100"
                                value="<?= $title ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small">URL</label>
                            <input type="text" 
                                class="form-control form-control-sm menu-item-url" 
                                placeholder="/page" 
                                maxlength="255"
                                value="<?= $url ?>">
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small"><?php echo LANG_CONTROLLER_ADMINMENU_FIELD_CSS_CLASS; ?></label>
                            <input type="text" 
                                class="form-control form-control-sm menu-item-class" 
                                placeholder="css-class" 
                                maxlength="50"
                                value="<?= $class ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-2">
                            <label class="form-label small"><?php echo LANG_CONTROLLER_ADMINMENU_FIELD_OPEN_IN; ?></label>
                            <select class="form-select form-select-sm menu-item-target">
                                <option value="_self" <?= $target === '_self' ? 'selected' : '' ?>><?php echo LANG_CONTROLLER_ADMINMENU_TARGET_SELF; ?></option>
                                <option value="_blank" <?= $target === '_blank' ? 'selected' : '' ?>><?php echo LANG_CONTROLLER_ADMINMENU_TARGET_BLANK; ?></option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="menu-children-container mt-3" style="display: <?= !empty($children) ? 'block' : 'none' ?>;">
                    <div class="border-top pt-3">
                        <h6 class="small text-muted mb-2"><?php echo LANG_CONTROLLER_ADMINMENU_NESTED_ITEMS; ?></h6>
                        <div class="menu-children sortable-menu">
                            <?php if (!empty($children)) { ?>
                                <?php foreach ($children as $childIndex => $child) { ?>
                                    <?= $this->renderMenuItem($child, $index . '_' . $childIndex) ?>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <button type="button" class="btn btn-outline-secondary btn-sm add-child-item">
                            <i class="bi bi-plus-circle me-1"></i><?php echo LANG_CONTROLLER_ADMINMENU_ADD_NESTED_ITEM; ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
    * Получение всех групп пользователей
    * @return array Массив групп пользователей
    */
    public function getUserGroups() {
        $userModel = new UserModel($this->db);
        $groups = $userModel->getAllGroups();

        $groups[] = [
            'id' => 'guest',
            'name' => LANG_CONTROLLER_ADMINMENU_GROUP_GUEST,
            'description' => LANG_CONTROLLER_ADMINMENU_GROUP_GUEST_DESC
        ];
        
        return $groups;
    }

}