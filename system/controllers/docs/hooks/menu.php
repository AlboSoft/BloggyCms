<?php

/**
* Хук для добавления пункта "Документация" последним в главное меню админ-панели
*/
\Event::listen('admin.menu.items', function($items) {
    $items[] = [
        'section'  => 'docs',
        'url'      => ADMIN_URL . '/docs',
        'icon'     => 'book',
        'title'    => LANG_DOCS_MENU_TITLE,
        'priority' => 9999
    ];
    return $items;
});
