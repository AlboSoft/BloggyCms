<?php
namespace seo\actions;

/**
* Действие сохранения настроек Schema.org
*/
class AdminSchema extends SeoAction {
    
    public function execute() {

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                if (!\CsrfToken::verify($_POST['csrf_token'] ?? '', 'seo_schema')) {
                    throw new \Exception(LANG_ACTION_SEO_ADMINSCHEMA_INVALID_CSRF);
                }

                $schemaSettings = [
                    'org_name' => trim($_POST['schema_org_name'] ?? ''),
                    'org_logo' => trim($_POST['schema_org_logo'] ?? ''),
                    'org_type' => $_POST['schema_org_type'] ?? 'Organization',
                    'org_url' => trim($_POST['schema_org_url'] ?? BASE_URL),
                    'social_facebook' => trim($_POST['schema_social_facebook'] ?? ''),
                    'social_twitter' => trim($_POST['schema_social_twitter'] ?? ''),
                    'social_instagram' => trim($_POST['schema_social_instagram'] ?? ''),
                    'social_telegram' => trim($_POST['schema_social_telegram'] ?? ''),
                    'social_vk' => trim($_POST['schema_social_vk'] ?? ''),
                    'social_youtube' => trim($_POST['schema_social_youtube'] ?? ''),
                    'contact_email' => trim($_POST['schema_contact_email'] ?? ''),
                    'contact_phone' => trim($_POST['schema_contact_phone'] ?? '')
                ];

                if (!empty($schemaSettings['contact_email']) && !filter_var($schemaSettings['contact_email'], FILTER_VALIDATE_EMAIL)) {
                    throw new \Exception(LANG_ACTION_SEO_ADMINSCHEMA_INVALID_EMAIL);
                }

                $this->seoModel->saveSchemaSettings($schemaSettings);
                
                if (class_exists('\SettingsHelper')) {
                    \SettingsHelper::clearCache('seo_schema');
                }
                
                \Notification::success(LANG_ACTION_SEO_ADMINSCHEMA_SUCCESS);
                
            } catch (\Exception $e) {
                \Notification::error(sprintf(LANG_ACTION_SEO_ADMINSCHEMA_ERROR, $e->getMessage()));
            }
        }

        $this->redirect(ADMIN_URL . '/seo?tab=schema');
    }
}