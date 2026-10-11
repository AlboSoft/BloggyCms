<?php
return [
    'admin/settings' => ['controller' => 'AdminSettings', 'action' => 'adminIndex', 'admin' => true],
    'admin/settings/reset' => ['controller' => 'AdminSettings', 'action' => 'reset', 'admin' => true],
    'admin/upload/settings-image' => ['controller' => 'Settings', 'action' => 'uploadImage', 'admin' => true]
];