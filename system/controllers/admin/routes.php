<?php
return [
    'admin' => ['controller' => 'Admin', 'action' => 'index', 'admin' => true],
    'admin/login' => ['controller' => 'Admin', 'action' => 'login'],
    'admin/logout' => ['controller' => 'Admin', 'action' => 'logout', 'admin' => true],
    'admin/check-updates' => ['controller' => 'Admin', 'action' => 'checkUpdates', 'admin' => true],
    'admin/controllers' => ['controller' => 'Controllers', 'action' => 'adminIndex', 'admin' => true],
    'admin/delete-install-folder' => ['controller' => 'Admin', 'action' => 'deleteInstallFolder', 'admin' => true],
    'admin/stats/data' => ['controller' => 'AdminStats', 'action' => 'getStatsData', 'admin' => true],
    'admin/stats/export-html' => ['controller' => 'AdminStats', 'action' => 'exportHtml', 'admin' => true]
];