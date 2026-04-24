<?php
namespace seo\actions;

/**
* Действие сохранения настроек sitemap
*/
class AdminSitemap extends SeoAction {
    public function execute() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $settings = [
                    'enabled' => isset($_POST['enabled']) ? 1 : 0,
                    'include_posts' => isset($_POST['include_posts']) ? 1 : 0,
                    'include_pages' => isset($_POST['include_pages']) ? 1 : 0,
                    'include_categories' => isset($_POST['include_categories']) ? 1 : 0,
                    'include_tags' => isset($_POST['include_tags']) ? 1 : 0,
                    'max_posts' => (int)($_POST['max_posts'] ?? 1000),
                    'cache_enabled' => isset($_POST['cache_enabled']) ? 1 : 0,
                    'cache_lifetime' => (int)($_POST['cache_lifetime'] ?? 3600)
                ];

                $this->seoModel->saveSettings('seo_sitemap', $settings);
                \Notification::success(LANG_ACTION_SEO_ADMINSITEMAP_SUCCESS);
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_SEO_ADMINSITEMAP_ERROR, $e->getMessage()));
            }
        }

        $this->redirect(ADMIN_URL . '/seo');
    }
}