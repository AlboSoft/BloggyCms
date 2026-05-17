<?php

/**
* Контроллер комментариев
*/
class CommentController extends Controller {
    
    private $commentModel;
    private $postModel;
    private $userModel;
    private $categoryModel;
    
    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_COMMENTS_MANIFEST_NAME,
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => LANG_CONTROLLER_COMMENTS_MANIFEST_DESCRIPTION
    ];
    
    /**
    * Конструктор контроллера комментариев
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->commentModel = new CommentModel($db);
        $this->postModel = new PostModel($db);
        $this->userModel = new UserModel($db);
        $this->categoryModel = new CategoryModel($db);
        
        AuthHelper::init();
    }

    /**
    * Возвращает системное имя контроллера
    * @return string
    */
    public function getSystemName() {
        return 'comments';
    }

    /**
    * Проверка типа запроса
    * @return bool true если запрос является AJAX-запросом
    */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Получение аватара пользователя из данных комментария
    * @param array $comment Данные комментария
    * @return string URL аватара пользователя
    */
    public function getUserAvatarFromComment($comment) {

        if (!empty($comment['author_avatar'])) {
            return $this->formatAvatarUrl($comment['author_avatar']);
        }
        
        if (!empty($comment['user_id'])) {
            try {
                $user = $this->userModel->getById($comment['user_id']);
                if ($user && !empty($user['avatar'])) {
                    return $this->formatAvatarUrl($user['avatar']);
                }
            } catch (Exception $e) {}
        }
        
        return BASE_URL . '/uploads/avatars/default.png';
    }
    
    /**
    * Форматирование URL аватара
    * @param string $avatar Значение аватара из базы данных
    * @return string Полный URL аватара
    */
    private function formatAvatarUrl($avatar) {
        if (empty($avatar) || $avatar === 'default.jpg' || $avatar === 'default.png') {
            return BASE_URL . '/uploads/avatars/default.png';
        }
        
        if (strpos($avatar, 'http') === 0) {
            return $avatar;
        } elseif (strpos($avatar, '/') === 0) {
            return BASE_URL . $avatar;
        } elseif (strpos($avatar, '/') === false) {
            return BASE_URL . '/uploads/avatars/' . $avatar;
        } else {
            return BASE_URL . '/' . $avatar;
        }
    }
    
    /**
    * Получение отображаемого имени пользователя из комментария
    * @param array $comment Данные комментария
    * @return string Имя пользователя для отображения
    */
    public function getUserDisplayName($comment) {
        if (!empty($comment['author_display_name'])) {
            return $comment['author_display_name'];
        }
        
        if (!empty($comment['author_username'])) {
            return $comment['author_username'];
        }
        
        if (!empty($comment['author_name'])) {
            return $comment['author_name'];
        }
        
        if (!empty($comment['user_id'])) {
            try {
                $user = $this->userModel->getById($comment['user_id']);
                if ($user) {
                    if (!empty($user['display_name'])) {
                        return $user['display_name'];
                    } elseif (!empty($user['username'])) {
                        return $user['username'];
                    }
                }
            } catch (Exception $e) {}
        }
        
        return LANG_CONTROLLER_COMMENTS_ANONYMOUS;
    }
    
    /**
    * Получение данных комментария с расширенной информацией о пользователе, правах и группах
    */
    public function getCommentWithUserData($comment) {

        $parentId = isset($comment['parent_id']) && $comment['parent_id'] !== null 
            ? (int)$comment['parent_id'] 
            : null;
        
        $userId = isset($comment['user_id']) ? (int)$comment['user_id'] : null;
        $currentUserId = Auth::getUserId();
        
        $canEdit = AuthHelper::canEditComment($userId);
        $canDelete = AuthHelper::canDeleteComment($userId);
        $canReply = AuthHelper::canAddComment();
        $isOwnComment = $currentUserId && $userId && $currentUserId == $userId;
        
        $userGroups = [];
        if ($userId) {
            try {
                $groupIds = $this->userModel->getUserGroups($userId);
                
                if (!empty($groupIds)) {
                    foreach ($groupIds as $groupId) {
                        $group = $this->userModel->getGroupById($groupId);
                        if ($group) {
                            $userGroups[] = [
                                'id' => (int)$group['id'],
                                'name' => $group['name']
                            ];
                        }
                    }
                }
            } catch (Exception $e) {}
        }
        
        $isAdmin = false;
        if ($userId) {
            try {
                $user = $this->userModel->getById($userId);
                $isAdmin = $user && (!empty($user['is_admin']) || $user['is_admin'] == 1);
            } catch (Exception $e) {}
        }
        
        $showLastAchievement = \UserModel::isShowLastAchievementEnabled();
        $lastAchievement = null;
        
        if ($showLastAchievement && $userId) {
            try {
                $lastAchievement = $this->userModel->getLastAchievement($userId);
            } catch (Exception $e) {}
        }
        
        $createdAt = $comment['created_at'];
        $now = time();
        $commentTime = strtotime($createdAt);
        
        if (($now - $commentTime) < 300) {
            $displayDate = LANG_CONTROLLER_COMMENTS_JUST_NOW;
        } else {
            $displayDate = date('d.m.Y H:i', $commentTime);
        }
        
        $wasEdited = false;
        $updatedAtDisplay = null;
        
        if (!empty($comment['updated_at'])) {
            $updatedAt = strtotime($comment['updated_at']);
            $createdAtTime = strtotime($comment['created_at']);
            $wasEdited = ($updatedAt - $createdAtTime) > 1;
            
            if ($wasEdited) {
                $updatedAtDisplay = date('d.m.Y H:i', $updatedAt);
            }
        }
        
        return [
            'id' => (int)$comment['id'],
            'post_id' => (int)$comment['post_id'],
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => $comment['content'],
            'status' => $comment['status'],
            'created_at' => $displayDate,
            'updated_at' => $updatedAtDisplay,
            'was_edited' => $wasEdited,
            'author_name' => $this->getUserDisplayName($comment),
            'author_username' => $comment['author_username'] ?? null,
            'author_avatar' => $this->getUserAvatarFromComment($comment),
            'is_pending' => $comment['status'] === 'pending',
            'is_own_comment' => $isOwnComment,
            'is_admin' => $isAdmin,
            'user_groups' => $userGroups,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'can_reply' => $canReply,
            'show_last_achievement' => $showLastAchievement,
            'last_achievement' => $lastAchievement
        ];
    }
    
    /**
    * Получение комментариев для поста с обработанными данными пользователей
    * @param int $postId ID поста
    * @param bool $includePending Включать ли комментарии на модерации
     */
    public function getCommentsByPostWithUserData($postId, $includePending = false) {
        $viewAllPending = AuthHelper::canViewAllComments();
        $comments = $this->commentModel->getCommentsByPost($postId, $includePending || $viewAllPending);
        $processedComments = [];
        foreach ($comments as $comment) {
            $processedComments[] = $this->getCommentWithUserData($comment);
        }
        $structuredComments = $this->buildCommentTree($processedComments);
        
        $totalCount = $this->countCommentsRecursive($structuredComments);
        
        return [
            'tree' => $structuredComments,
            'total' => $totalCount,
            'raw_count' => count($comments)
        ];
    }

    /**
    * Рекурсивный подсчет всех комментариев в дереве
    * @param array $comments Массив комментариев с вложенными ответами
    * @return int Общее количество комментариев
    */
    private function countCommentsRecursive($comments) {
        $count = 0;
        foreach ($comments as $comment) {
            $count++;
            if (!empty($comment['replies'])) {
                $count += $this->countCommentsRecursive($comment['replies']);
            }
        }
        return $count;
    }

    /**
    * Получение одного комментария по ID (для AJAX-запросов)
    * @param int $id ID комментария
    * @return void Отправляет JSON-ответ
    */
    public function getCommentAction($id) {
        if (!$this->isAjaxRequest()) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => LANG_CONTROLLER_COMMENTS_AJAX_ONLY]);
            return;
        }
        
        try {
            $comment = $this->commentModel->getCommentById($id);
            
            if (!$comment) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => LANG_CONTROLLER_COMMENTS_NOT_FOUND]);
                return;
            }
            
            $commentWithUserData = $this->getCommentWithUserData($comment);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'comment' => $commentWithUserData
            ]);
            
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => LANG_CONTROLLER_COMMENTS_GET_ERROR . $e->getMessage()
            ]);
        }
    }

    /**
    * Построение дерева комментариев с поддержкой бесконечной вложенности
    * @param array $comments Плоский массив комментариев
    * @return array Иерархическое дерево комментариев
    */
    private function buildCommentTree($comments) {
        $commentsByParent = [];
        foreach ($comments as $comment) {
            $parentId = isset($comment['parent_id']) ? (int)$comment['parent_id'] : 0;
            if (!isset($commentsByParent[$parentId])) {
                $commentsByParent[$parentId] = [];
            }
            $commentsByParent[$parentId][] = $comment;
        }
        
        $buildTree = function($parentId) use (&$buildTree, $commentsByParent) {
            $result = [];
            
            if (isset($commentsByParent[$parentId])) {
                foreach ($commentsByParent[$parentId] as $comment) {
                    $children = $buildTree($comment['id']);
                    if (!empty($children)) {
                        $comment['replies'] = $children;
                    }
                    $result[] = $comment;
                }
            }
            
            usort($result, function($a, $b) {
                return strtotime($a['created_at']) - strtotime($b['created_at']);
            });
            
            return $result;
        };
        
        return $buildTree(0);
    }
    
    /**
    * Действие: Страница управления комментариями в админ-панели
    * @return mixed
    */
    public function adminIndexAction() {
        $action = new \comments\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование комментария в админ-панели
    * @param int $id ID комментария
    * @return mixed
    */
    public function adminEditAction($id) {
        $action = new \comments\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление комментария в админ-панели 
    * @param int $id ID комментария
    * @return mixed
    */
    public function adminDeleteAction($id) {
        $action = new \comments\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Одобрение комментария в админ-панели
    * @param int $id ID комментария
    * @return mixed
    */
    public function adminApproveAction($id) {
        $action = new \comments\actions\AdminApprove($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Добавление нового комментария
    * @return mixed
    */
    public function addAction() {
        $action = new \comments\actions\Add($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление комментария пользователем
    * @param int $id ID комментария
    * @return mixed
    */
    public function deleteAction($id) {
        $action = new \comments\actions\Delete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование комментария пользователем
    * @param int $id ID комментария
    * @return mixed
    */
    public function editAction($id) {
        $action = new \comments\actions\Edit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
}