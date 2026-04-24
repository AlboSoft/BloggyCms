<?php

namespace posts\actions;

/**
* Действие проверки пароля для защищенных постов
* @package posts\actions
*/
class CheckPassword extends PostAction {
    
    /**
    * Метод выполнения проверки пароля
    * @return void
    */
    public function execute() {

        $postId = $this->params['id'] ?? null;
        
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => LANG_ACTION_POSTS_CHECKPASSWORD_NO_POST_ID]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['password'])) {
            $this->redirect(BASE_URL . '/post/' . $post['slug'] . '?error=password');
            return;
        }
        
        $password = $_POST['password'];
        
        $result = $this->postModel->checkPassword($postId, $password);
        
        if ($result) {
            if (!isset($_SESSION['post_access'])) {
                $_SESSION['post_access'] = [];
            }
            $_SESSION['post_access'][$postId] = true;
            
            $redirectUrl = $_POST['redirect'] ?? BASE_URL . '/posts';
            $this->redirect($redirectUrl);
        } else {
            $post = $this->postModel->getById($postId);
            if ($post) {
                $this->redirect(BASE_URL . '/post/' . $post['slug'] . '?error=password');
            } else {
                $this->redirect(BASE_URL);
            }
        }
    }
}