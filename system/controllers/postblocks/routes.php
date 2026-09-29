<?php
return [
    'admin/post-blocks' => ['controller' => 'AdminPostBlock', 'action' => 'adminIndex', 'admin' => true],
    'admin/post-blocks/edit/{system_name}' => ['controller' => 'AdminPostBlock', 'action' => 'edit', 'admin' => true],
    'admin/post-blocks/edit' => ['controller' => 'AdminPostBlock', 'action' => 'edit', 'admin' => true],
    'admin/post-blocks/get-preview' => ['controller' => 'AdminPostBlock', 'action' => 'adminGetPreview', 'admin' => true],
    'admin/post-blocks/get-previews' => ['controller' => 'AdminPostBlock', 'action' => 'adminGetPreviews', 'admin' => true],
    'admin/post-blocks/get-block-blueprint' => ['controller' => 'AdminPostBlock', 'action' => 'getBlockBlueprint', 'admin' => true],
    'admin/post-blocks/get-settings-form' => ['controller' => 'AdminPostBlock', 'action' => 'getSettingsForm', 'admin' => true],
    'admin/post-blocks/save-block' => ['controller' => 'AdminPostBlock', 'action' => 'saveBlock', 'admin' => true],
    'admin/post-blocks/upload-block-files' => ['controller' => 'AdminPostBlock', 'action' => 'uploadBlockFiles', 'admin' => true],
    'admin/post-blocks/get-default-content' => ['controller' => 'AdminPostBlock', 'action' => 'getDefaultContent', 'admin' => true],
    'admin/post-blocks/get-default-settings' => ['controller' => 'AdminPostBlock', 'action' => 'getDefaultSettings', 'admin' => true],
    'admin/post-blocks/get-template' => ['controller' => 'AdminPostBlock', 'action' => 'getTemplate', 'admin' => true],
    'admin/post-blocks/save-block-data' => ['controller' => 'AdminPostBlock', 'action' => 'saveBlockData', 'admin' => true],
    'admin/post-blocks/get-presets' => ['controller' => 'AdminPostBlock', 'action' => 'getPresets', 'admin' => true],
    'admin/post-blocks/save-settings' => ['controller' => 'AdminPostBlock', 'action' => 'saveSettings', 'admin' => true],
    'admin/post-blocks/render-sample' => ['controller' => 'AdminPostBlock', 'action' => 'renderSample', 'admin' => true],
    'admin/post-blocks/create-preset' => ['controller' => 'AdminPostBlock', 'action' => 'createPreset', 'admin' => true],
    'admin/post-blocks/update-preset' => ['controller' => 'AdminPostBlock', 'action' => 'updatePreset', 'admin' => true],
    'admin/post-blocks/delete-preset' => ['controller' => 'AdminPostBlock', 'action' => 'deletePreset', 'admin' => true]
];