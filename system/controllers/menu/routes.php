<?php
return [
    'admin/menu' => ['controller' => 'AdminMenu', 'action' => 'adminIndex', 'admin' => true],
    'admin/menu/create' => ['controller' => 'AdminMenu', 'action' => 'create', 'admin' => true],
    'admin/menu/edit/{id}' => ['controller' => 'AdminMenu', 'action' => 'edit', 'admin' => true],
    'admin/menu/delete/{id}' => ['controller' => 'AdminMenu', 'action' => 'delete', 'admin' => true],
    'admin/menu/items/{id}' => ['controller' => 'AdminMenu', 'action' => 'items', 'admin' => true],
    'admin/menu/item/create/{menuId}' => ['controller' => 'AdminMenu', 'action' => 'itemCreate', 'admin' => true],
    'admin/menu/item/edit/{id}' => ['controller' => 'AdminMenu', 'action' => 'itemEdit', 'admin' => true],
    'admin/menu/item/delete/{id}' => ['controller' => 'AdminMenu', 'action' => 'itemDelete', 'admin' => true],
    'admin/menu/reorder' => ['controller' => 'AdminMenu', 'action' => 'reorder', 'admin' => true],
    'admin/menu/preview/{id}' => ['controller' => 'AdminMenu', 'action' => 'preview', 'admin' => true],
];