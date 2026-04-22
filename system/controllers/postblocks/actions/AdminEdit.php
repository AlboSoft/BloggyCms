<?php

namespace postblocks\actions;

/**
* Действие редактирования настроек постблока в административной панели 
* @package postblocks\actions
*/
class AdminEdit extends PostBlockAction {
    
    /**
    * Метод выполнения редактирования постблока
    * @return void
    */
    public function execute() {

        $systemName = $_GET['system_name'] ?? '';
        
        if (empty($systemName)) {
            \Notification::error(LANG_ACTION_POSTBLOCKS_ADMINEDIT_SYSTEM_NAME_NOT_SPECIFIED);
            $this->redirect(ADMIN_URL . '/post-blocks');
            return;
        }

        $postBlock = $this->postBlockManager->getPostBlock($systemName);
        if (!$postBlock) {
            \Notification::error(LANG_ACTION_POSTBLOCKS_ADMINEDIT_BLOCK_NOT_FOUND);
            $this->redirect(ADMIN_URL . '/post-blocks');
            return;
        }
        
        $this->addBreadcrumb(LANG_ACTION_POSTBLOCKS_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_POSTBLOCKS_ADMINEDIT_BREADCRUMB_POSTBLOCKS, ADMIN_URL . '/post-blocks');
        $this->addBreadcrumb(LANG_ACTION_POSTBLOCKS_ADMINEDIT_BREADCRUMB_EDIT . $postBlock['name']);

        $settings = $this->postBlockModel->getBlockSettings($systemName);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleSettingsUpdate($systemName, $postBlock, $settings);
            return;
        }

        $this->render('admin/post_blocks/edit', [
            'postBlock' => $postBlock,
            'settings' => $settings,
            'shortcodes' => $postBlock['class']->getShortcodes(),
            'pageTitle' => LANG_ACTION_POSTBLOCKS_ADMINEDIT_PAGE_TITLE . $postBlock['name']
        ]);
    }

    /**
    * Обрабатывает обновление настроек блока из POST-запроса
    * @param string $systemName Системное имя блока
    * @param array $postBlock Данные постблока
    * @param array $currentSettings Текущие настройки (не используются)
    * @return void
    */
    private function handleSettingsUpdate($systemName, $postBlock, $currentSettings) {
        $enableInPosts = isset($_POST['enable_in_posts']) ? 1 : 0;
        $enableInPages = isset($_POST['enable_in_pages']) ? 1 : 0;
        $template = $_POST['template'] ?? '';
        $success = $this->postBlockModel->updateBlockSettings($systemName, [
            'enable_in_posts' => $enableInPosts,
            'enable_in_pages' => $enableInPages,
            'template' => $template
        ]);

        if ($success) {
            \Notification::success(LANG_ACTION_POSTBLOCKS_ADMINEDIT_SUCCESS);
        } else {
            \Notification::error(LANG_ACTION_POSTBLOCKS_ADMINEDIT_SAVE_ERROR);
        }

        $this->redirect(ADMIN_URL . '/post-blocks/edit?system_name=' . $systemName);
    }
}