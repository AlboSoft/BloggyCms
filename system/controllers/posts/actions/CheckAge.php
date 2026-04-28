<?php

namespace posts\actions;

class CheckAge extends PostAction {
    
    public function execute() {
        $postId = $this->params['id'] ?? null;
        
        if (!$postId) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $post = $this->postModel->getById($postId);
        
        if (!$post) {
            $this->redirect(BASE_URL);
            return;
        }
        
        $settingsModel = new \SettingsModel($this->db);
        $settings = $settingsModel->get('controller_posts');
        $minAge = (int)($settings['adult_min_age'] ?? 18);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $day = (int)($_POST['day'] ?? 0);
            $month = (int)($_POST['month'] ?? 0);
            $year = (int)($_POST['year'] ?? 0);
            
            if ($day && $month && $year) {
                $birthDate = new \DateTime("{$year}-{$month}-{$day}");
                $today = new \DateTime();
                $age = $today->diff($birthDate)->y;
                
                if ($age >= $minAge) {
                    $rememberDecision = $settings['adult_remember_decision'] ?? true;
                    
                    if ($rememberDecision) {
                        $_SESSION['adult_verified'] = true;
                    }
                    $_SESSION['adult_verified_post_' . $postId] = true;
                    
                    $redirectUrl = $_SESSION['adult_redirect_after'] ?? BASE_URL . '/post/' . $post['slug'];
                    unset($_SESSION['adult_redirect_after']);
                    $this->redirect($redirectUrl);
                    return;
                } else {
                    $error = sprintf(LANG_ACTION_POSTS_CHECKAGE_TOO_YOUNG, $minAge);
                }
            } else {
                $error = LANG_ACTION_POSTS_CHECKAGE_INVALID_DATE;
            }
        }
        
        $this->addBreadcrumb(LANG_ACTION_POSTS_CHECKAGE_BREADCRUMB_HOME, BASE_URL);
        $this->addBreadcrumb($post['title'], BASE_URL . '/post/' . $post['slug']);
        $this->addBreadcrumb(LANG_ACTION_POSTS_CHECKAGE_BREADCRUMB_VERIFY);
        $this->setPageTitle(LANG_ACTION_POSTS_CHECKAGE_PAGE_TITLE);
        
        $this->render('front/posts/check_age', [
            'post' => $post,
            'min_age' => $minAge,
            'error' => $error ?? null
        ]);
    }
}