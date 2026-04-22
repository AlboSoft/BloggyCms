<?php

namespace menu\actions;

/**
* Абстрактный базовый класс для всех действий модуля управления меню
* @package menu\actions
*/
abstract class MenuAction {
    
    protected $db;
    protected $params;
    protected $controller;
    protected $menuModel;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Конструктор класса действия
    * @param object $db Подключение к базе данных
    * @param array $params Параметры запроса (по умолчанию [])
    */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->menuModel = new \MenuModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Устанавливает контроллер, вызывающий действие
    * @param object $controller Контроллер
    * @return void
    */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
    * Абстрактный метод выполнения действия
    * @return void
    */
    abstract public function execute();
    
    /**
    * Добавляет элемент в хлебные крошки
    * @param string $title Название элемента
    * @param string|null $url URL элемента
    * @return self
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы
    * @param string $title Заголовок
    * @return self
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
    * Рендерит шаблон с переданными данными
    * @param string $template Путь к шаблону
    * @param array $data Данные для передачи в шаблон
    * @throws \Exception Если контроллер не установлен
    * @return void
    */
    protected function render($template, $data = []) {
        if ($this->controller) {
            if (!isset($data['breadcrumbs'])) {
                $data['breadcrumbs'] = $this->breadcrumbs;
            }
            if (!isset($data['title']) && $this->pageTitle) {
                $data['title'] = $this->pageTitle;
            }
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
    * Выполняет перенаправление на указанный URL
    * @param string $url URL для перенаправления
    * @return void
    */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
    * Рендерит отдельный пункт меню для административного интерфейса
    * @param array $item Данные пункта меню
    * @param int $index Индекс пункта в структуре
    * @return string HTML-код пункта меню
    */
    protected function renderMenuItem($item, $index) {
        if ($this->controller && method_exists($this->controller, 'renderMenuItem')) {
            return $this->controller->renderMenuItem($item, $index);
        }
        return '';
    }
    
    /**
    * Рендерит HTML-код для предпросмотра меню
    * @param array $structure Структура меню
    * @return string HTML-код меню для предпросмотра
    */
    protected function renderMenuPreview($structure) {
        $html = '<ul class="nav">';
        
        foreach ($structure as $item) {
            $html .= $this->renderPreviewMenuItem($item);
        }
        
        $html .= '</ul>';
        return $html;
    }

    /**
    * Рекурсивно рендерит отдельный пункт меню для предпросмотра
    * @param array $item Данные пункта меню
    * @param int $level Уровень вложенности (по умолчанию 0)
    * @return string HTML-код пункта меню
    */
    protected function renderPreviewMenuItem($item, $level = 0) {
        $class = $item['class'] ?? '';
        $target = $item['target'] ?? '_self';
        $title = html($item['title'] ?? '', ENT_QUOTES, 'UTF-8');
        $url = html($item['url'] ?? '#', ENT_QUOTES, 'UTF-8');
        
        $html = '<li class="nav-item' . ($class ? ' ' . $class : '') . '">';
        $html .= '<a href="' . $url . '" target="' . $target . '" class="nav-link">' . $title . '</a>';
        
        if (!empty($item['children'])) {
            $html .= '<ul class="nav flex-column ms-3">';
            foreach ($item['children'] as $child) {
                $html .= $this->renderPreviewMenuItem($child, $level + 1);
            }
            $html .= '</ul>';
        }
        
        $html .= '</li>';
        return $html;
    }

    /**
    * Рендерит HTML-код настроек видимости для пункта меню
    * @param array $item Данные пункта меню (по умолчанию [])
    * @return string HTML-код настроек видимости
    */
    protected function renderVisibilitySettings($item = []) {
        $groups = $this->getUserGroups();
        $currentShowTo = $item['visibility']['show_to_groups'] ?? [];
        $currentHideFrom = $item['visibility']['hide_from_groups'] ?? [];
        
        ob_start();
        ?>
        <div class="row mt-3">
            <div class="col-md-6">
                <label class="form-label small">
                    <?php echo bloggy_icon('bs', 'eye', '14', '#000', 'me-1'); ?>
                    <?php echo LANG_ACTION_MENU_MENUACTION_SHOW_TO_GROUPS; ?>
                </label>
                <select class="form-select form-select-sm menu-item-show-to" multiple size="4">
                    <option value=""><?php echo LANG_ACTION_MENU_MENUACTION_ALL_GROUPS; ?></option>
                    <?php foreach ($groups as $group) { ?>
                        <option value="<?= html($group['id']) ?>" 
                            <?= in_array($group['id'], $currentShowTo) ? 'selected' : '' ?>>
                            <?= html($group['name']) ?>
                        </option>
                    <?php } ?>
                </select>
                <div class="form-text small"><?php echo LANG_ACTION_MENU_MENUACTION_SHOW_TO_GROUPS_HINT; ?></div>
            </div>
            
            <div class="col-md-6">
                <label class="form-label small">
                    <?php echo bloggy_icon('bs', 'eye-slash', '14', '#000', 'me-1'); ?>
                    <?php echo LANG_ACTION_MENU_MENUACTION_HIDE_FROM_GROUPS; ?>
                </label>
                <select class="form-select form-select-sm menu-item-hide-from" multiple size="4">
                    <option value=""><?php echo LANG_ACTION_MENU_MENUACTION_NO_HIDE; ?></option>
                    <?php foreach ($groups as $group) { ?>
                        <option value="<?= html($group['id']) ?>" 
                            <?= in_array($group['id'], $currentHideFrom) ? 'selected' : '' ?>>
                            <?= html($group['name']) ?>
                        </option>
                    <?php } ?>
                </select>
                <div class="form-text small"><?php echo LANG_ACTION_MENU_MENUACTION_HIDE_FROM_GROUPS_HINT; ?></div>
            </div>
        </div>
        
        <div class="alert alert-info mt-2 p-2 small">
            <?php echo bloggy_icon('bs', 'info-circle', '14', '#000', 'me-1'); ?>
            <strong><?php echo LANG_ACTION_MENU_MENUACTION_PRIORITY_TITLE; ?></strong> <?php echo LANG_ACTION_MENU_MENUACTION_PRIORITY_TEXT; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
    * Получает список всех групп пользователей
    * @return array Массив групп с полями id, name, description
    */
    protected function getUserGroups() {
        $userModel = new \UserModel($this->db);
        $groups = $userModel->getAllGroups();
        
        $groups[] = [
            'id' => 'guest',
            'name' => LANG_ACTION_MENU_MENUACTION_GROUP_GUEST,
            'description' => LANG_ACTION_MENU_MENUACTION_GROUP_GUEST_DESC
        ];
        
        return $groups;
    }
    
    /**
    * Возвращает менеджер хлебных крошек
    * @return \BreadcrumbsManager
    */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
}