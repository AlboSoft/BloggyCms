<?php

namespace icons\actions;

/**
* Действие отображения списка иконок в админ-панели
* @package icons\actions
*/
class AdminIndex {
    
    protected $controller;
    protected $breadcrumbs;
    protected $pageTitle;
    
    /**
    * Установка контроллера для действия
    * @param object $controller Объект контроллера
    * @return void
    */
    public function setController($controller) {
        $this->controller = $controller;
        $this->breadcrumbs = new \BreadcrumbsManager();
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
    }
    
    /**
    * Добавляет элемент в хлебные крошки
    */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
    * Устанавливает заголовок страницы 
    */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
    * Метод выполнения отображения списка иконок
    * @return void
    */
    public function execute() {
        $this->addBreadcrumb(LANG_ACTION_ICONS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_ICONS_ADMININDEX_BREADCRUMB_ICONS);
        $this->setPageTitle(LANG_ACTION_ICONS_ADMININDEX_PAGE_TITLE);
        
        $icons = $this->getAllIcons();
        
        $this->render('admin/icons/index', [
            'icons' => $icons,
            'pageTitle' => LANG_ACTION_ICONS_ADMININDEX_PAGE_TITLE
        ]);
    }
    
    /**
    * Получение всех иконок из директорий шаблона
    * @return array Структурированный массив с информацией об иконках, сгруппированный по наборам
    */
    private function getAllIcons() {
        $icons = [];
        $iconsDir = TEMPLATES_PATH . '/default/admin/icons/';
        
        $files = glob($iconsDir . '*.svg');
        
        foreach ($files as $file) {
            $set = basename($file, '.svg');
            $content = file_get_contents($file);
            
            preg_match_all('/<symbol\s+id="([^"]+)"/', $content, $matches);
            
            if (!empty($matches[1])) {
                $icons[$set] = [
                    'name' => $set,
                    'count' => count($matches[1]),
                    'icons' => array_map(function($id) use ($set) {
                        return [
                            'id' => $id,
                            'preview' => bloggy_icon($set, $id, '48 48'),
                            'code' => "<?php echo bloggy_icon('{$set}', '{$id}'); ?>"
                        ];
                    }, $matches[1])
                ];
            }
        }
        
        return $icons;
    }
    
    /**
    * Рендеринг шаблона с данными
    * @param string $template Путь к файлу шаблона
    * @param array $data Массив данных для передачи в шаблон
    * @return void
    * @throws \Exception Если контроллер не установлен
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
}