<?php
/**
 * Scroll To Top - Habr Pro - no radius
 */
$scrollThreshold = (int)($settings['scroll_threshold'] ?? 300);
$animationDuration = (int)($settings['animation_duration'] ?? 300);
$position = $settings['position'] ?? 'bottom-right';
$offsetBottom = (int)($settings['offset_bottom'] ?? 20);
$offsetSide = (int)($settings['offset_side'] ?? 20);
$size = $settings['size'] ?? 'md';
$bgColor = $settings['background_color'] ?? '#000000';
$textColor = $settings['text_color'] ?? '#ffffff';

$sizeMap = ['sm' => 36, 'md' => 40, 'lg' => 44];
$buttonSize = $sizeMap[$size] ?? 40;
$customId = !empty($settings['custom_id']) ? html($settings['custom_id']) : 'scroll-to-top-btn';

$style = "position:fixed;z-index:9999;display:flex;align-items:center;justify-content:center;width:{$buttonSize}px;height:{$buttonSize}px;background:{$bgColor};color:{$textColor};border:1px solid {$bgColor};cursor:pointer;opacity:0;visibility:hidden;transition:opacity .15s,visibility .15s;";
if ($position === 'bottom-right') $style .= "bottom:{$offsetBottom}px;right:{$offsetSide}px;";
else $style .= "bottom:{$offsetBottom}px;left:{$offsetSide}px;";

$buttonClasses = 'scroll-to-top-btn';
if (!empty($settings['custom_css_class'])) $buttonClasses .= ' ' . html($settings['custom_css_class']);
?>
<button type="button" id="<?php echo $customId; ?>" class="<?php echo $buttonClasses; ?>" style="<?php echo $style; ?>" aria-label="вверх" data-scroll-threshold="<?php echo $scrollThreshold; ?>" data-animation-duration="<?php echo $animationDuration; ?>">↑</button>
