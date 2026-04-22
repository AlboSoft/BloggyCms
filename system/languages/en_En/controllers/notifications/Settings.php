<?php

define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_FIELDSET_COMMENTS', 'Comments');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_COMMENTS_VARIABLES', 'Show notifications');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_COMMENTS_OPTION_ALL', 'All without exception');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_COMMENTS_OPTION_PENDING', 'Only those requiring moderation');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_FIELDSET_ERRORS', 'Error Notifications');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_NOTIFY_ON_NEW', 'Notify about new system errors');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_NOTIFY_ON_NEW_HINT', 'When a new error appears in the logs (Debug controller), a notification will be sent to administrators');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPES', 'Error types to notify about');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPES_HINT', 'Select the error types to be notified about');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_ERROR', 'PHP Errors');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_WARNING', 'Warnings');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_NOTICE', 'Notices');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_TYPE_EXCEPTION', 'Exceptions');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_ONLY_UNFIXED', 'Notify only about unfixed errors');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_ONLY_UNFIXED_HINT', 'Repeated notifications about the same error will not be sent until it is marked as fixed');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_THROTTLE', 'Minimum interval between notifications (minutes)');
define('LANG_CONTROLLER_NOTIFICATIONS_SETTINGS_ERRORS_THROTTLE_HINT', 'To avoid spamming notifications during mass errors');