<?php
/**
 * Список категорий
 */

$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'center';
$displayStyle = $settings['display_style'] ?? 'cards';
$columns = (int)($settings['columns'] ?? 3);
$showPostCount = !empty($settings['show_post_count']);
$showIcon = !empty($settings['show_icon']);
$gradientCards = !empty($settings['gradient_cards']);
$showHierarchy = !empty($settings['show_hierarchy']);
$imageStyle = $settings['image_style'] ?? 'icon';
$imageSize = $settings['image_size'] ?? 'md';
$imageRounded = !empty($settings['image_rounded']);
$imageShadow = !empty($settings['image_shadow']);
$customStyles = [];
if($theme === 'custom') {
    if(!empty($settings['background_color'])) {
        $customStyles[] = '--bg-color: ' . html($settings['background_color']);
    }
    if(!empty($settings['text_color'])) {
        $customStyles[] = '--text-color: ' . html($settings['text_color']);
    }
}
if(!empty($settings['accent_color'])) {
    $customStyles[] = '--accent-color: ' . html($settings['accent_color']);
    
    $hex = ltrim($settings['accent_color'], '#');
    if(strlen($hex) === 6) {
        $rgb = hexdec(substr($hex, 0, 2)) . ', ' . 
               hexdec(substr($hex, 2, 2)) . ', ' . 
               hexdec(substr($hex, 4, 2));
        $customStyles[] = '--accent-rgb: ' . $rgb;
    }
}
if(!empty($settings['card_background'])) {
    $customStyles[] = '--card-bg: ' . html($settings['card_background']);
}

if($gradientCards && !empty($settings['accent_color'])) {
    $customStyles[] = '--gradient-start: ' . html($settings['accent_color']) . '20';
    $customStyles[] = '--gradient-end: ' . html($settings['accent_color']) . '05';
}

$paddingTop = (int)($settings['padding_top'] ?? 80);
$paddingBottom = (int)($settings['padding_bottom'] ?? 80);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';
$sectionClass = 'categories-list';
$sectionClass .= ' theme-' . $theme;
$sectionClass .= ' align-' . $align;
if(!empty($settings['custom_css_class'])) {
    $sectionClass .= ' ' . html($settings['custom_css_class']);
}

$categories = $this->categories ?? [];

function getCategoryImageUrl($category) {
    if (!empty($category['image'])) {
        if (strpos($category['image'], 'http') === 0 || strpos($category['image'], '/') === 0) {
            return $category['image'];
        }
        return '/uploads/images/' . $category['image'];
    }
    return '/templates/' . DEFAULT_TEMPLATE . '/front/assets/img/default-category.jpg';
}

function renderCategoryImage($category, $settings) {
    $imageStyle = $settings['image_style'] ?? 'icon';
    $imageSize = $settings['image_size'] ?? 'md';
    $imageRounded = !empty($settings['image_rounded']);
    $imageShadow = !empty($settings['image_shadow']);
    
    $html = '';
    
    $sizeClasses = [
        'sm' => 'size-sm',
        'md' => 'size-md',
        'lg' => 'size-lg'
    ];
    
    $imageClass = 'category-image';
    $imageClass .= ' ' . ($sizeClasses[$imageSize] ?? 'size-md');
    
    if($imageRounded) {
        $imageClass .= ' rounded';
    }
    if($imageShadow) {
        $imageClass .= ' shadow';
    }
    
    $imageUrl = getCategoryImageUrl($category);
    
    switch($imageStyle) {
        case 'icon':
            $html .= '<div class="category-icon">';
            $html .= bloggy_icon('bs', 'folder', '48 48', 'currentColor', '');
            $html .= '</div>';
            break;
            
        case 'thumbnail':
            $html .= '<div class="' . $imageClass . ' thumbnail">';
            $html .= '<img src="' . $imageUrl . '" alt="' . html($category['name']) . '" loading="lazy">';
            $html .= '</div>';
            break;
            
        case 'cover':
            $html .= '<div class="category-cover" style="background-image: url(\'' . $imageUrl . '\')">';
            $html .= '<div class="category-cover-overlay"></div>';
            $html .= '</div>';
            break;
            
        case 'background':
            $html .= '<div class="category-background" style="background-image: url(\'' . $imageUrl . '\')"></div>';
            break;
            
        case 'side':
            $html .= '<div class="' . $imageClass . ' side">';
            $html .= '<img src="' . $imageUrl . '" alt="' . html($category['name']) . '" loading="lazy">';
            $html .= '</div>';
            break;
            
        default:
            break;
    }
    
    return $html;
}

function renderCategory($category, $settings, $level = 0) {
    $html = '';
    $imageStyle = $settings['image_style'] ?? 'icon';
    
    switch ($settings['display_style'] ?? 'cards') {
        case 'cards':
            $cardClass = 'category-card';
            
            if (!empty($settings['gradient_cards'])) {
                $cardClass .= ' gradient';
            }
            
            if ($imageStyle === 'cover') {
                $cardClass .= ' has-cover';
            } elseif ($imageStyle === 'background') {
                $cardClass .= ' has-background';
            }
            
            $html .= '<a href="/category/' . html($category['slug']) . '" class="' . $cardClass . '">';
            $html .= renderCategoryImage($category, $settings);
            $contentClass = ($imageStyle === 'cover' || $imageStyle === 'background') ? 'category-content-overlay' : '';
            
            if(!empty($contentClass)) {
                $html .= '<div class="' . $contentClass . '">';
            }
            
            if ($imageStyle !== 'cover' && $imageStyle !== 'background') {
                $html .= '<div class="category-name">' . html($category['name']) . '</div>';
            } else {
                $html .= '<div class="category-name overlay">' . html($category['name']) . '</div>';
            }
            
            if (!empty($category['description']) && $imageStyle !== 'cover') {
                $html .= '<div class="category-description">' . html($category['description']) . '</div>';
            }
            
            if (!empty($settings['show_post_count'])) {
                $count = $category['posts_count'] ?? 0;
    
                $countText = ($lang === 'ru') 
                ? plural($count, ['пост', 'поста', 'постов']) 
                : ($count === 1 ? 'post' : 'posts');

                $html .= '<div class="category-count">' . $count . ' ' . $countText . '</div>';
            }
            
            if(!empty($contentClass)) {
                $html .= '</div>';
            }
            
            $html .= '</a>';
            
            if (!empty($category['children']) && !empty($settings['show_hierarchy'])) {
                $html .= '<div class="category-children" style="margin-left: ' . (($level + 1) * 1.5) . 'rem">';
                foreach ($category['children'] as $child) {
                    $html .= renderCategory($child, $settings, $level + 1);
                }
                $html .= '</div>';
            }
            break;
            
        case 'list':
            $html .= '<a href="/category/' . html($category['slug']) . '" class="category-list-item">';
            $html .= '<div class="category-list-left">';
            
            if ($imageStyle === 'thumbnail' || $imageStyle === 'side') {
                $html .= '<div class="category-list-image">';
                $html .= '<img src="' . getCategoryImageUrl($category) . '" alt="' . html($category['name']) . '" loading="lazy">';
                $html .= '</div>';
            } else {
                $html .= '<div class="category-list-icon">';
                $html .= bloggy_icon('bs', 'folder', '24 24', 'currentColor', '');
                $html .= '</div>';
            }
            
            $html .= '<span class="category-list-name">' . html($category['name']) . '</span>';
            $html .= '</div>';
            
            if (!empty($settings['show_post_count'])) {
                $html .= '<div class="category-count">' . ($category['posts_count'] ?? 0) . '</div>';
            }
            
            $html .= '</a>';
            
            if (!empty($category['children']) && !empty($settings['show_hierarchy'])) {
                $html .= '<div style="margin-left: 2rem;">';
                foreach ($category['children'] as $child) {
                    $html .= renderCategory($child, $settings, $level + 1);
                }
                $html .= '</div>';
            }
            break;
            
        case 'compact':
            $html .= '<a href="/category/' . html($category['slug']) . '" class="category-compact-item">';
            $html .= bloggy_icon('bs', 'folder', '16 16', 'currentColor', '');
            
            $html .= '<span>' . html($category['name']) . '</span>';
            
            if (!empty($settings['show_post_count'])) {
                $html .= '<span class="category-compact-count">(' . ($category['posts_count'] ?? 0) . ')</span>';
            }
            
            $html .= '</a>';
            
            if (!empty($category['children']) && !empty($settings['show_hierarchy'])) {
                $html .= '<div style="margin-left: 2rem;">';
                foreach ($category['children'] as $child) {
                    $html .= renderCategory($child, $settings, $level + 1);
                }
                $html .= '</div>';
            }
            break;
    }
    
    return $html;
}
?>

<section id="<?php echo html($settings['custom_id'] ?? ''); ?>" 
         class="<?php echo $sectionClass; ?>" 
         style="<?php echo implode('; ', $customStyles); ?>">
    <div class="container">
        
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
        <div class="header">
            
            <?php if(!empty($settings['badge'])) { ?>
            <div class="badge"><?php echo html($settings['badge']); ?></div>
            <?php } ?>
            
            <?php if(!empty($settings['title'])) { ?>
            <h2><?php echo $settings['title']; ?></h2>
            <?php } ?>
            
            <?php if(!empty($settings['description'])) { ?>
            <div class="header-description"><?php echo nl2br(html($settings['description'])); ?></div>
            <?php } ?>
            
        </div>
        <?php } ?>
        
        <?php if(!empty($categories)) { ?>
            
            <?php if($displayStyle === 'cards' || $displayStyle === 'grid') { ?>
            <div class="categories-grid cols-<?php echo $columns; ?>">
                <?php foreach($categories as $category) {
                    echo renderCategory($category, $settings);
                } ?>
            </div>
            
            <?php } elseif($displayStyle === 'list') { ?>
            <div class="categories-list">
                <?php foreach($categories as $category) {
                    echo renderCategory($category, $settings);
                } ?>
            </div>
            
            <?php } elseif($displayStyle === 'compact') { ?>
            <div class="categories-compact">
                <?php foreach($categories as $category) {
                    echo renderCategory($category, $settings);
                } ?>
            </div>
            <?php } ?>
            
        <?php } else { ?>
        <div class="text-center py-5">
            <p class="text-muted">Нет доступных категорий</p>
        </div>
        <?php } ?>
        
    </div>
</section>
