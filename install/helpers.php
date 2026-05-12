<?php

/**
* Выводит SVG иконку из спрайта
*/
function install_icon($set, $icon, $size = null, $color = null, $class = null) {

    $iconsPath = '../templates/default/admin/icons/' . $set . '.svg';
    
    $attrs = [];
    
    $baseClass = 'icon icon-' . $icon;
    $attrs['class'] = $class ? $baseClass . ' ' . $class : $baseClass;
    
    if ($size) {
        $parts = explode(' ', $size);
        $attrs['width'] = $parts[0];
        $attrs['height'] = isset($parts[1]) ? $parts[1] : $parts[0];
    }
    
    if ($color) {
        $attrs['style'] = 'fill: ' . $color . ';';
    } else {
        $attrs['style'] = 'fill: currentColor;';
    }
    
    $attrsString = '';
    foreach ($attrs as $name => $value) {
        $attrsString .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
    }
    
    return sprintf(
        '<svg%s><use href="%s#%s"/></svg>',
        $attrsString,
        $iconsPath,
        htmlspecialchars($icon)
    );
}

function icon($set, $icon, $size = null, $color = null, $class = null) {
    return install_icon($set, $icon, $size, $color, $class);
}