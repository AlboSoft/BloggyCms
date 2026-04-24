<?php

class VersionHelper {
    
    private static $versionData = null;
    
    private static function loadVersionData() {
        if (self::$versionData !== null) {
            return;
        }
        
        $versionFile = BASE_PATH . '/system/config/version.ini';
        
        if (file_exists($versionFile)) {
            $data = parse_ini_file($versionFile, true);
            if ($data !== false) {
                self::$versionData = $data;
                return;
            }
        }
        
        self::$versionData = [
            'version' => [
                'major' => 1,
                'minor' => 0,
                'patch' => 0,
                'suffix' => '',
                'build' => 0,
                'date' => date('Y-m-d'),
                'name' => LANG_HELPER_VERSION_DEFAULT_NAME
            ]
        ];
    }
    
    public static function getVersion() {
        self::loadVersionData();
        $v = self::$versionData['version'];
        $version = $v['major'] . '.' . $v['minor'] . '.' . $v['patch'];
        if (!empty($v['suffix'])) {
            $version .= '-' . $v['suffix'];
        }
        return $version;
    }
    
    public static function getBuild() {
        self::loadVersionData();
        return (int)(self::$versionData['version']['build'] ?? 0);
    }
    
    public static function getVersionName() {
        self::loadVersionData();
        return self::$versionData['version']['name'] ?? '';
    }
    
    public static function getVersionDate() {
        self::loadVersionData();
        return self::$versionData['version']['date'] ?? '';
    }
    
    public static function checkUpdates() {
        return [
            'has_update' => false,
            'current_version' => self::getVersion(),
            'latest_version' => self::getVersion(),
            'message' => LANG_HELPER_VERSION_NO_UPDATES
        ];
    }
}