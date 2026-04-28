<?php
return [
    '' => ['controller' => 'Post', 'action' => 'index'],
    'posts' => ['controller' => 'Post', 'action' => 'all'],
    'post/{slug}' => ['controller' => 'Post', 'action' => 'show'],
    'post/like/{id}' => ['controller' => 'Post', 'action' => 'like'],
    'post/bookmark/{id}' => ['controller' => 'Post', 'action' => 'bookmark'],
    'user/bookmarks' => ['controller' => 'Post', 'action' => 'bookmarks'],
    'post/check-age/{id}' => ['controller' => 'Post', 'action' => 'checkAge'],
    'post/check-password/{id}' => ['controller' => 'Post', 'action' => 'checkPassword'],
    'admin/posts' => ['controller' => 'Post', 'action' => 'adminIndex', 'admin' => true],
    'admin/posts/create' => ['controller' => 'Post', 'action' => 'create', 'admin' => true],
    'admin/posts/edit/{id}' => ['controller' => 'Post', 'action' => 'edit', 'admin' => true],
    'admin/posts/delete/{id}' => ['controller' => 'Post', 'action' => 'delete', 'admin' => true],
    'admin/posts/toggle-status/{id}' => ['controller' => 'Post', 'action' => 'toggleStatus', 'admin' => true],
    'admin/posts/upload-featured-image' => ['controller' => 'Post', 'action' => 'uploadFeaturedImage', 'admin' => true],
    'admin/posts/upload-gallery-images' => ['controller' => 'Post', 'action' => 'uploadGalleryImages', 'admin' => true],
    'admin/posts/upload-block-image' => ['controller' => 'Post', 'action' => 'uploadBlockImage', 'admin' => true],
];