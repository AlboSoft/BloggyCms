<?php
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_METHOD', 'Неверный метод запроса');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_FILE_NOT_UPLOADED', 'Файл не загружен');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_FORMAT', 'Файл должен быть в формате ZIP');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_SAVE_ERROR', 'Не удалось сохранить файл');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_NO_PACKAGE_INI', 'Пакет должен содержать файл package.ini');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_VERSION_REQUIRED', 'Требуется версия %s, установлена %s');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_ALREADY_INSTALLED', 'Пакет уже установлен');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_NO_ZIPARCHIVE', 'PHP ZipArchive не установлен');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_CANT_OPEN_ZIP', 'Не удалось открыть ZIP-архив');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_CANT_READ_INI', 'Не удалось прочитать package.ini');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_INI_FORMAT', 'Неверный формат package.ini');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_TITLE', 'В package.ini отсутствует обязательный блок [info] с полем title');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_TITLE_TOO_LONG', 'Название пакета не должно превышать 64 символа');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_VERSION', 'В package.ini отсутствует обязательный блок [version] с полями major, minor, build');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_INSTALL_OR_UPDATE', 'В package.ini должен быть блок [install] или [update]');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_INSTALL_TYPE', 'Для блока [install] поле type должно быть "install"');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_UPDATE_TYPE', 'Для блока [update] поле type должно быть "update"');