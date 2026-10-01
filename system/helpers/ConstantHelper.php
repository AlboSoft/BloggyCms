<?php

/**
* Вспомогательный класс для безопасного получения значений констант
* и защиты от падения сайта (500) при отсутствии языковых констант в PHP 8+
*/
class ConstantHelper {
    
    /**
    * @var array Список недостающих констант, которые были автоматически определены как текст
    */
    private static $missingConstants = [];
    
    /**
    * @var array Список уже проверенных файлов в рамках текущего запроса
    */
    private static $scannedFiles = [];

    /**
    * Получить значение константы или имя константы, если она не определена
    * @param string $constantName Имя константы
    * @param mixed $defaultValue Значение по умолчанию (если не указано, вернется имя константы как есть)
    * @return mixed Значение константы или имя/значение по умолчанию
    */
    public static function get($constantName, $defaultValue = null) {
        if (defined($constantName) && !isset(self::$missingConstants[$constantName])) {
            return constant($constantName);
        }
        
        if (!defined($constantName)) {
            self::registerMissing($constantName);
        }
        
        if ($defaultValue === null) {
            return $constantName;
        }
        
        return $defaultValue;
    }
    
    /**
    * Проверить, определена ли константа в языковых файлах (не является авто-заглушкой)
    * @param string $constantName Имя константы
    * @return bool
    */
    public static function isDefined($constantName) {
        return defined($constantName) && !isset(self::$missingConstants[$constantName]);
    }

    /**
    * Проверить, числится ли константа как отсутствующая
    * @param string $constantName Имя константы
    * @return bool
    */
    public static function isMissing($constantName) {
        return isset(self::$missingConstants[$constantName]) || !defined($constantName);
    }

    /**
    * Получить список всех отсутствующих констант, обнаруженных при выполнении
    * @return array
    */
    public static function getMissingConstants() {
        return array_keys(self::$missingConstants);
    }

    /**
    * Зарегистрировать отсутствующую константу и определить её значением её собственное имя
    * @param string $constantName Имя константы (может включать namespace)
    * @param string $context Контекст (файл/строка), где обнаружена константа
    * @return void
    */
    public static function registerMissing($constantName, $context = '') {
        $constantName = trim((string)$constantName, '\\');
        if ($constantName === '') {
            return;
        }

        $shortName = $constantName;
        if (strpos($constantName, '\\') !== false) {
            $shortName = substr($constantName, strrpos($constantName, '\\') + 1);
        }

        if (!defined($shortName)) {
            @define($shortName, $shortName);
            self::$missingConstants[$shortName] = true;
            $logMsg = "[LANG MISSING] Undefined language constant: {$shortName}";
            if ($context !== '') {
                $logMsg .= " ({$context})";
            }
            error_log($logMsg);
        }

        if ($constantName !== $shortName && !defined($constantName)) {
            @define($constantName, $shortName);
        }
    }

    /**
    * Аварийный парсер языкового файла, если require_once упал с ParseError
    * Извлекает корректные вызовы define('LANG_...', '...') из поврежденного файла
    * @param string $filePath Путь к языковому файлу
    * @return int Количество восстановленных констант
    */
    public static function loadConstantsFromBrokenFile($filePath) {
        if (!is_file($filePath) || !is_readable($filePath)) {
            return 0;
        }

        $content = @file_get_contents($filePath);
        if ($content === false || $content === '') {
            return 0;
        }

        $count = 0;
        $pattern = '/define\s*\(\s*([\'"])([A-Za-z_][A-Za-z0-9_]*)\1\s*,\s*([\'"])((?:\\\\.|(?!\3).)*)\3\s*\)/s';
        if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $constName = $match[2];
                $quote = $match[3];
                $rawValue = $match[4];
                if ($quote === "'") {
                    $value = str_replace(["\\'", "\\\\"], ["'", "\\"], $rawValue);
                } else {
                    $value = stripcslashes($rawValue);
                }
                if (!defined($constName)) {
                    @define($constName, $value);
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
    * Сканирует конкретный PHP-файл и определяет все неопределенные константы LANG_* как их собственные имена
    * @param string $filePath Путь к PHP-файлу
    * @return void
    */
    public static function ensureFileConstants($filePath) {
        if (!$filePath || isset(self::$scannedFiles[$filePath])) {
            return;
        }
        self::$scannedFiles[$filePath] = true;

        if (!is_file($filePath) || !is_readable($filePath)) {
            return;
        }

        $content = @file_get_contents($filePath);
        if ($content === false || strpos($content, 'LANG_') === false) {
            return;
        }

        if (preg_match_all('/\b(LANG_[A-Z0-9_]+)\b/', $content, $matches)) {
            foreach (array_unique($matches[1]) as $constName) {
                if (!defined($constName)) {
                    self::registerMissing($constName, $filePath);
                }
            }
        }
    }

    /**
    * Глобальная проверка и авто-определение всех используемых в проекте констант LANG_*
    * Использует кеширование по времени модификации файлов (filemtime) для максимальной производительности
    * @param string|null $rootPath Корень проекта
    * @param string|null $cacheDir Папка кеша
    * @return void
    */
    public static function ensureAllConstants($rootPath = null, $cacheDir = null) {
        $rootPath = $rootPath ?: (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2));
        $cacheDir = $cacheDir ?: (defined('CACHE_DIR') ? CACHE_DIR : $rootPath . '/cache');

        $cacheFile = rtrim($cacheDir, '/\\') . '/lang_constants_scan.cache';
        $cacheData = [];
        $cacheDirty = false;

        if (is_file($cacheFile) && is_readable($cacheFile)) {
            $raw = @file_get_contents($cacheFile);
            if ($raw !== false) {
                $decoded = @unserialize($raw, ['allowed_classes' => false]);
                if (is_array($decoded)) {
                    $cacheData = $decoded;
                }
            }
        }

        $scanDirs = [
            $rootPath . '/system',
            $rootPath . '/templates'
        ];

        $allConstants = [];

        foreach ($scanDirs as $scanDir) {
            if (!is_dir($scanDir)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($scanDir, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $fileInfo) {
                if (!$fileInfo->isFile() || strtolower($fileInfo->getExtension()) !== 'php') {
                    continue;
                }

                $path = $fileInfo->getPathname();
                $mtime = $fileInfo->getMTime();

                if (isset($cacheData[$path]) && isset($cacheData[$path]['mtime']) && $cacheData[$path]['mtime'] === $mtime) {
                    $fileConsts = $cacheData[$path]['consts'] ?? [];
                } else {
                    $fileConsts = [];
                    $content = @file_get_contents($path);
                    if ($content !== false && strpos($content, 'LANG_') !== false) {
                        if (preg_match_all('/\b(LANG_[A-Z0-9_]+)\b/', $content, $matches)) {
                            $fileConsts = array_values(array_unique($matches[1]));
                        }
                    }
                    $cacheData[$path] = [
                        'mtime' => $mtime,
                        'consts' => $fileConsts
                    ];
                    $cacheDirty = true;
                }

                self::$scannedFiles[$path] = true;

                foreach ($fileConsts as $constName) {
                    $allConstants[$constName] = $path;
                }
            }
        }

        if ($cacheDirty && is_dir($cacheDir) && is_writable($cacheDir)) {
            @file_put_contents($cacheFile, serialize($cacheData), LOCK_EX);
        }

        foreach ($allConstants as $constName => $sourcePath) {
            if (!defined($constName)) {
                self::registerMissing($constName, $sourcePath);
            }
        }
    }

    /**
    * Перехватывает фатальную ошибку PHP 8+ "Undefined constant ...",
    * определяет недостающую константу как текст её имени и позволяет повторить выполнение без 500 ошибки
    * @param \Throwable $error Исключение/ошибка
    * @return bool true, если это была ошибка неопределенной константы и она была исправлена
    */
    public static function handleUndefinedConstantError($error) {
        if (!($error instanceof \Throwable)) {
            return false;
        }

        $message = $error->getMessage();
        if (!preg_match('/Undefined constant [\'"]([^\'"]+)[\'"]/i', $message, $matches)) {
            return false;
        }

        $missingConst = $matches[1];
        $file = $error->getFile();
        $line = $error->getLine();

        self::registerMissing($missingConst, "{$file}:{$line}");

        if ($file && is_file($file)) {
            unset(self::$scannedFiles[$file]);
            self::ensureFileConstants($file);
        }

        return true;
    }
    
    /**
    * Получить список всех определенных констант, начинающихся с LANG_
    * @return array
    */
    public static function getDefinedLanguageConstants() {
        $allConstants = get_defined_constants();
        $langConstants = [];
        
        foreach ($allConstants as $name => $value) {
            if (strpos($name, 'LANG_') === 0) {
                $langConstants[$name] = $value;
            }
        }
        
        return $langConstants;
    }
}

/**
* Глобальная функция для удобного получения значения константы
* @param string $constantName Имя константы
* @param mixed $defaultValue Значение по умолчанию
* @return mixed
*/
function constant_safe($constantName, $defaultValue = null) {
    return ConstantHelper::get($constantName, $defaultValue);
}
