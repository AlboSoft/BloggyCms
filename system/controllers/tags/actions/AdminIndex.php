<?php

namespace tags\actions;

/**
* Действие отображения списка всех тегов в административной панели
* @package tags\actions
*/
class AdminIndex extends TagAction {
    
    /**
    * Метод выполнения отображения списка тегов
    * @return void
    */
    public function execute() {

        $this->addBreadcrumb(LANG_ACTION_TAGS_ADMININDEX_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_ACTION_TAGS_ADMININDEX_BREADCRUMB_TAGS);
        
        try {
            $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : null;
            $hasImage = isset($_GET['has_image']) ? $_GET['has_image'] : null;
            $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name_asc';
            
            $allTags = $this->tagModel->getAll();
            $filteredTags = $this->filterTags($allTags, $searchTerm, $hasImage);
            $sortedTags = $this->sortTags($filteredTags, $sort);
            
            $hints = [
                LANG_ACTION_TAGS_ADMININDEX_HINT_1,
                LANG_ACTION_TAGS_ADMININDEX_HINT_2,
                LANG_ACTION_TAGS_ADMININDEX_HINT_3,
                LANG_ACTION_TAGS_ADMININDEX_HINT_4,
                LANG_ACTION_TAGS_ADMININDEX_HINT_5
            ];
            
            $randomHint = $hints[array_rand($hints)];

            $settings = [
                'default_image' => \SettingsHelper::get('controller_tags', 'default_image'),
                'tag_prefix' => \SettingsHelper::get('controller_tags', 'tag_prefix', '#'),
                'show_info' => \SettingsHelper::get('controller_tags', 'show_info')
            ];

            $stats = [
                'total' => count($sortedTags),
                'with_image' => count(array_filter($sortedTags, function($tag) {
                    return !empty($tag['image']);
                })),
                'without_image' => count(array_filter($sortedTags, function($tag) {
                    return empty($tag['image']);
                }))
            ];

            $this->render('admin/tags/index', [
                'tags' => $sortedTags,
                'randomHint' => $randomHint,
                'pageTitle' => LANG_ACTION_TAGS_ADMININDEX_PAGE_TITLE,
                'settings' => $settings,
                'searchTerm' => $searchTerm,
                'hasImageFilter' => $hasImage,
                'currentSort' => $sort,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_TAGS_ADMININDEX_ERROR);
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
    * Фильтрует теги по поисковому запросу и наличию изображения
    * @param array $tags Массив всех тегов
    * @param string|null $searchTerm Поисковый запрос
    * @param string|null $hasImage Фильтр по наличию изображения ('yes', 'no', null)
    * @return array Отфильтрованный массив тегов
    */
    private function filterTags($tags, $searchTerm = null, $hasImage = null) {
        $filteredTags = [];
        
        foreach ($tags as $tag) {
            if ($searchTerm !== null && !empty($searchTerm)) {
                if (stripos($tag['name'], $searchTerm) === false && 
                    stripos($tag['slug'], $searchTerm) === false &&
                    stripos($tag['description'] ?? '', $searchTerm) === false) {
                    continue;
                }
            }
            
            if ($hasImage !== null) {
                $hasImageValue = !empty($tag['image']);
                
                if ($hasImage === 'yes' && !$hasImageValue) {
                    continue;
                }
                if ($hasImage === 'no' && $hasImageValue) {
                    continue;
                }
            }
            
            $filteredTags[] = $tag;
        }
        
        return $filteredTags;
    }
    
    /**
    * Сортирует теги по указанному критерию
    * @param array $tags Массив тегов для сортировки
    * @param string $sort Критерий сортировки
    * @return array Отсортированный массив тегов
    */
    private function sortTags($tags, $sort) {
        switch ($sort) {
            case 'name_desc':
                usort($tags, function($a, $b) {
                    return strcmp($b['name'], $a['name']);
                });
                break;
            case 'posts_desc':
                usort($tags, function($a, $b) {
                    return ($b['posts_count'] ?? 0) - ($a['posts_count'] ?? 0);
                });
                break;
            case 'posts_asc':
                usort($tags, function($a, $b) {
                    return ($a['posts_count'] ?? 0) - ($b['posts_count'] ?? 0);
                });
                break;
            case 'created_desc':
                usort($tags, function($a, $b) {
                    return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
                });
                break;
            case 'created_asc':
                usort($tags, function($a, $b) {
                    return strtotime($a['created_at'] ?? '0') - strtotime($b['created_at'] ?? '0');
                });
                break;
            case 'name_asc':
            default:
                usort($tags, function($a, $b) {
                    return strcmp($a['name'], $b['name']);
                });
                break;
        }
        
        return $tags;
    }
}