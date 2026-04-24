<?php
namespace seo\actions;

/**
* Действие сохранения настроек RSS
*/
class AdminRss extends SeoAction {
    public function execute() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $settings = [
                    'enabled' => isset($_POST['enabled']) ? 1 : 0,
                    'posts_limit' => (int)($_POST['posts_limit'] ?? 20),
                    'include_full_content' => isset($_POST['include_full_content']) ? 1 : 0,
                    'copyright' => $_POST['copyright'] ?? '',
                    'language' => $_POST['language'] ?? 'ru-ru'
                ];

                $this->seoModel->saveSettings('seo_rss', $settings);
                \Notification::success(LANG_ACTION_SEO_ADMINRSS_SUCCESS);
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_SEO_ADMINRSS_ERROR, $e->getMessage()));
            }
        }

        $this->redirect(ADMIN_URL . '/seo');
    }
}