<?php
namespace seo\actions;

/**
* Действие тестовой отправки IndexNow
*/
class AdminTestIndexNow extends SeoAction {
    
    public function execute() {
        
        try {
            $settings = $this->seoModel->getIndexNowSettings();
            $host = $this->seoModel->getHost();
            
            if ($this->seoModel->isLocalhost($host)) {
                \Notification::warning(sprintf(
                    LANG_ACTION_SEO_ADMINTESTINDEXNOW_LOCALHOST_WARNING,
                    $host
                ));
                $this->redirect(ADMIN_URL . '/seo?tab=indexnow');
                return;
            }
            
            $testUrl = rtrim(BASE_URL, '/') . '/';
            
            if (!preg_match('#^https?://#', $testUrl)) {
                \Notification::error(sprintf(
                    LANG_ACTION_SEO_ADMINTESTINDEXNOW_INVALID_BASE_URL,
                    BASE_URL
                ));
                $this->redirect(ADMIN_URL . '/seo?tab=indexnow');
                return;
            }
            
            $results = [];
            
            if (!empty($settings['ya_key'])) {
                $yaResult = $this->seoModel->sendIndexNowPing('ya_key', $testUrl);
                $results['yandex'] = $yaResult;
            }
            
            if (!empty($settings['bing_key'])) {
                $bingResult = $this->seoModel->sendIndexNowPing('bing_key', $testUrl);
                $results['bing'] = $bingResult;
            }
            
            if (empty($results)) {
                \Notification::error(LANG_ACTION_SEO_ADMINTESTINDEXNOW_NO_KEYS);
            } else {
                $successCount = 0;
                $messages = [];
                
                foreach ($results as $engine => $result) {
                    if ($result['success']) {
                        $successCount++;
                        $messages[] = sprintf(LANG_ACTION_SEO_ADMINTESTINDEXNOW_RESULT_SUCCESS, ucfirst($engine), $result['code']);
                    } else {
                        $errorMsg = $result['error'] ?? LANG_ACTION_SEO_ADMINTESTINDEXNOW_UNKNOWN_ERROR;
                        if (strlen($errorMsg) > 150) {
                            $errorMsg = substr($errorMsg, 0, 150) . '...';
                        }
                        $messages[] = sprintf(LANG_ACTION_SEO_ADMINTESTINDEXNOW_RESULT_ERROR, ucfirst($engine), $result['code'], $errorMsg);
                    }
                }
                
                if ($successCount > 0) {
                    \Notification::success(LANG_ACTION_SEO_ADMINTESTINDEXNOW_SUCCESS . implode(", ", $messages));
                } else {
                    \Notification::error(LANG_ACTION_SEO_ADMINTESTINDEXNOW_FAILURE . implode(", ", $messages));
                }
            }
            
        } catch (\Exception $e) {
            \Notification::error(sprintf(LANG_ACTION_SEO_ADMINTESTINDEXNOW_EXCEPTION, $e->getMessage()));
        }
        
        $this->redirect(ADMIN_URL . '/seo?tab=indexnow');
    }
}