<?php
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_METHOD', 'Invalid request method');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_FILE_NOT_UPLOADED', 'File not uploaded');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_FORMAT', 'File must be in ZIP format');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_SAVE_ERROR', 'Failed to save file');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_NO_PACKAGE_INI', 'Package must contain package.ini file');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_VERSION_REQUIRED', 'Version %s required, %s installed');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_ALREADY_INSTALLED', 'Package already installed');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_NO_ZIPARCHIVE', 'PHP ZipArchive not installed');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_CANT_OPEN_ZIP', 'Failed to open ZIP archive');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_CANT_READ_INI', 'Failed to read package.ini');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_INI_FORMAT', 'Invalid package.ini format');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_TITLE', 'package.ini missing required [info] block with title field');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_TITLE_TOO_LONG', 'Package title must not exceed 64 characters');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_VERSION', 'package.ini missing required [version] block with major, minor, build fields');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_MISSING_INSTALL_OR_UPDATE', 'package.ini must have [install] or [update] block');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_INSTALL_TYPE', 'For [install] block type field must be "install"');
define('LANG_CONTROLLER_ADDONS_ACTION_ANALYZE_INVALID_UPDATE_TYPE', 'For [update] block type field must be "update"');