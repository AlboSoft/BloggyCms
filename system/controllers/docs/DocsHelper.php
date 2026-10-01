<?php

namespace docs;

class DocsHelper {

    /**
    * Возвращает базовый путь к папке документации
    */
    public static function getDocsPath() {
        return ROOT_PATH . '/docs';
    }

    /**
    * Возвращает структурированное дерево всех разделов и документов
    */
    public static function getStructure() {
        $base = self::getDocsPath();
        $sections = [
            'overview' => [
                'title' => LANG_DOCS_SECTION_OVERVIEW,
                'icon'  => 'info-circle',
                'files' => []
            ],
            'administrator' => [
                'title' => LANG_DOCS_SECTION_ADMIN,
                'icon'  => 'person-gear',
                'files' => []
            ],
            'developer' => [
                'title' => LANG_DOCS_SECTION_DEV,
                'icon'  => 'code-slash',
                'files' => []
            ],
            'reference' => [
                'title' => LANG_DOCS_SECTION_REF,
                'icon'  => 'journal-bookmark',
                'files' => []
            ],
        ];

        if (file_exists($base . '/README.md')) {
            $sections['overview']['files'][] = [
                'key'   => 'README.md',
                'title' => self::getFileTitle($base . '/README.md', 'Главная документация'),
                'path'  => 'README.md'
            ];
        }

        foreach (['administrator', 'developer', 'reference'] as $dir) {
            $fullDir = $base . '/' . $dir;
            if (!is_dir($fullDir)) continue;

            $files = scandir($fullDir);
            sort($files);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || substr($file, -3) !== '.md') continue;
                $relPath = $dir . '/' . $file;
                $sections[$dir]['files'][] = [
                    'key'   => $relPath,
                    'title' => self::getFileTitle($fullDir . '/' . $file, $file),
                    'path'  => $relPath
                ];
            }
        }

        return $sections;
    }

    /**
    * Извлекает первый H1 заголовок из Markdown файла
    */
    public static function getFileTitle($filePath, $fallback = '') {
        if (!file_exists($filePath)) return $fallback;
        $content = file_get_contents($filePath);
        if (preg_match('/^#\s+(.+)$/m', $content, $m)) {
            return trim($m[1]);
        }
        return $fallback;
    }

    /**
    * Сопоставление раздела админки (или контроллера) с файлом документации
    */
    public static function getDocFileForSection($section) {
        $section = strtolower(trim((string)$section, '/'));

        $map = [
            'posts'             => 'administrator/02-posty-i-stranicy.md',
            'categories'        => 'administrator/03-kategorii-tegi-kommentarii.md',
            'tags'              => 'administrator/03-kategorii-tegi-kommentarii.md',
            'comments'          => 'administrator/03-kategorii-tegi-kommentarii.md',
            'pages'             => 'administrator/02-posty-i-stranicy.md',
            'html-blocks'       => 'administrator/04-bloki-i-fragmenty.md',
            'html_blocks'       => 'administrator/04-bloki-i-fragmenty.md',
            'post-blocks'       => 'administrator/04-bloki-i-fragmenty.md',
            'postblocks'        => 'administrator/04-bloki-i-fragmenty.md',
            'fragments'         => 'administrator/04-bloki-i-fragmenty.md',
            'fields'            => 'administrator/05-polya.md',
            'users'             => 'administrator/06-polzovateli-prava-dostizheniya.md',
            'user-groups'       => 'administrator/06-polzovateli-prava-dostizheniya.md',
            'user-achievements' => 'administrator/06-polzovateli-prava-dostizheniya.md',
            'menu'              => 'administrator/07-menyu.md',
            'settings'          => 'administrator/08-nastroyki.md',
            'seo'               => 'administrator/09-seo.md',
            'templates'         => 'administrator/10-tema.md',
            'addons'            => 'administrator/11-dopolneniya-i-obnovleniya.md',
            'updates'           => 'administrator/11-dopolneniya-i-obnovleniya.md',
            'check-updates'     => 'administrator/11-dopolneniya-i-obnovleniya.md',
            'debug'             => 'administrator/12-obsluzhivanie-i-bezopasnost.md',
            'icons'             => 'administrator/08-nastroyki.md',
            'language'          => 'administrator/08-nastroyki.md',
            'controllers'       => 'developer/03-svoy-kontroller.md',
            'search'            => 'administrator/01-osnovy-raboty-s-panelyu.md',
            'search-history'    => 'administrator/01-osnovy-raboty-s-panelyu.md',
            'notifications'     => 'administrator/01-osnovy-raboty-s-panelyu.md',
        ];

        return $map[$section] ?? 'README.md';
    }

    /**
    * Безопасное получение и рендеринг документа
    */
    public static function loadDocument($docKey) {
        $base = self::getDocsPath();
        $docKey = trim((string)$docKey, '/');

        if (empty($docKey)) {
            $docKey = 'README.md';
        }

        if (strpos($docKey, '..') !== false || strpos($docKey, '\\') !== false) {
            $docKey = 'README.md';
        }

        $filePath = realpath($base . '/' . $docKey);
        $realBase = realpath($base);

        if (!$filePath || !$realBase || strpos($filePath, $realBase) !== 0 || !file_exists($filePath)) {
            return [
                'success' => false,
                'title'   => LANG_DOCS_NOT_FOUND_TITLE,
                'html'    => '<div class="alert alert-warning">' . LANG_DOCS_NOT_FOUND_TEXT . '</div>',
                'key'     => $docKey
            ];
        }

        $rawMarkdown = file_get_contents($filePath);
        $title = self::getFileTitle($filePath, basename($filePath));
        $html = self::renderMarkdown($rawMarkdown);

        return [
            'success' => true,
            'title'   => $title,
            'html'    => $html,
            'key'     => $docKey
        ];
    }

    /**
    * Полноценный Markdown парсер в чистый HTML
    */
    public static function renderMarkdown($text) {
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $codeBlocks = [];
        $text = preg_replace_callback('/```([a-zA-Z0-9_\-]*)\n([\s\S]*?)\n```/', function($m) use (&$codeBlocks) {
            $lang = trim($m[1]);
            $code = $m[2];
            $escaped = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $idx = count($codeBlocks);
            $langBadge = $lang ? '<span class="code-lang-badge">' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '</span>' : '';
            $codeBlocks[] = '<div class="docs-code-wrapper">' . $langBadge . '<pre><code class="language-' . htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') . '">' . $escaped . '</code></pre></div>';
            return "__DOCS_CODE_BLOCK_{$idx}__";
        }, $text);

        $inlineCodes = [];
        $text = preg_replace_callback('/`([^`\n]+)`/', function($m) use (&$inlineCodes) {
            $code = $m[1];
            $idx = count($inlineCodes);
            $escaped = htmlspecialchars($code, ENT_QUOTES, 'UTF-8');
            $inlineCodes[] = '<code class="docs-inline-code">' . $escaped . '</code>';
            return "__DOCS_INLINE_CODE_{$idx}__";
        }, $text);

        $lines = explode("\n", $text);
        $out = [];
        $inList = false;
        $listType = null;
        $inTable = false;
        $tableRows = [];

        $flushList = function() use (&$out, &$inList, &$listType) {
            if ($inList) {
                $out[] = "</{$listType}>";
                $inList = false;
                $listType = null;
            }
        };

        $flushTable = function() use (&$out, &$inTable, &$tableRows) {
            if ($inTable && !empty($tableRows)) {
                $html = ['<div class="table-responsive my-3"><table class="table table-bordered table-striped docs-table align-middle">'];
                foreach ($tableRows as $i => $row) {
                    if ($i === 0) {
                        $html[] = '<thead class="table-light"><tr>';
                        foreach ($row as $cell) {
                            $html[] = '<th>' . $cell . '</th>';
                        }
                        $html[] = '</tr></thead><tbody>';
                    } else {
                        $html[] = '<tr>';
                        foreach ($row as $cell) {
                            $html[] = '<td>' . $cell . '</td>';
                        }
                        $html[] = '</tr>';
                    }
                }
                if (count($tableRows) > 0) {
                    $html[] = '</tbody>';
                }
                $html[] = '</table></div>';
                $out[] = implode("\n", $html);
                $inTable = false;
                $tableRows = [];
            }
        };

        $inlineFormat = function($s) {
            $s = preg_replace('/!\[([^\]]*)\]\(([^)]+)\)/', '<img src="$2" alt="$1" class="img-fluid rounded my-2 border">', $s);
            $s = preg_replace_callback('/\[([^\]]+)\]\(([^)]+)\)/', function($m) {
                $label = $m[1];
                $url = trim($m[2]);

                if (preg_match('/\.md(?:#.*)?$/', $url) && !preg_match('/^https?:\/\//i', $url)) {
                    $url = ltrim($url, './');
                    return '<a href="' . ADMIN_URL . '/docs/view?doc=' . rawurlencode($url) . '" data-doc-link="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" class="docs-internal-link">' . $label . '</a>';
                }

                $target = (preg_match('/^https?:\/\//i', $url)) ? ' target="_blank" rel="noopener noreferrer"' : '';
                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' . $target . '>' . $label . '</a>';
            }, $s);

            $s = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $s);
            $s = preg_replace('/(?<!\*)\*([^*]+)\*(?!\*)/', '<em>$1</em>', $s);
            return $s;
        };

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (strlen($trimmed) >= 2 && $trimmed[0] === '|' && substr($trimmed, -1) === '|') {
                if (preg_match('/^\|[\s\-:|]+\|$/', $trimmed)) {
                    continue;
                }
                $inner = substr($trimmed, 1, -1);
                $cells = explode('|', $inner);
                $cellsFormatted = [];
                foreach ($cells as $c) {
                    $cellsFormatted[] = $inlineFormat(trim($c));
                }
                if (!$inTable) {
                    $flushList();
                    $inTable = true;
                    $tableRows = [];
                }
                $tableRows[] = $cellsFormatted;
                continue;
            } else {
                $flushTable();
            }

            if ($trimmed === '') {
                $flushList();
                continue;
            }

            if (preg_match('/^__DOCS_CODE_BLOCK_\d+__$/', $trimmed)) {
                $flushList();
                $out[] = $trimmed;
                continue;
            }

            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $hm)) {
                $flushList();
                $lvl = strlen($hm[1]);
                $htext = $inlineFormat($hm[2]);
                $id = 'heading-' . substr(md5($hm[2]), 0, 8);
                $out[] = "<h{$lvl} id=\"{$id}\" class=\"docs-heading docs-h{$lvl}\">{$htext}</h{$lvl}>";
                continue;
            }

            if ($trimmed[0] === '>') {
                $flushList();
                $btext = $inlineFormat(trim(ltrim($trimmed, '> ')));
                $out[] = "<blockquote class=\"docs-blockquote\"><p class=\"mb-0\">{$btext}</p></blockquote>";
                continue;
            }

            if (preg_match('/^(-{3,}|\*{3,}|_{3,})$/', $trimmed)) {
                $flushList();
                $out[] = '<hr class="my-4">';
                continue;
            }

            if (preg_match('/^[\*\-]\s+(.+)$/', $trimmed, $lm)) {
                if (!$inList || $listType !== 'ul') {
                    $flushList();
                    $out[] = '<ul class="docs-list">';
                    $inList = true;
                    $listType = 'ul';
                }
                $out[] = '<li>' . $inlineFormat($lm[1]) . '</li>';
                continue;
            }

            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $lm)) {
                if (!$inList || $listType !== 'ol') {
                    $flushList();
                    $out[] = '<ol class="docs-list">';
                    $inList = true;
                    $listType = 'ol';
                }
                $out[] = '<li>' . $inlineFormat($lm[1]) . '</li>';
                continue;
            }

            $flushList();
            $out[] = '<p class="docs-p">' . $inlineFormat($trimmed) . '</p>';
        }

        $flushList();
        $flushTable();

        $result = implode("\n", $out);

        foreach ($codeBlocks as $i => $block) {
            $result = str_replace("__DOCS_CODE_BLOCK_{$i}__", $block, $result);
        }

        foreach ($inlineCodes as $i => $icode) {
            $result = str_replace("__DOCS_INLINE_CODE_{$i}__", $icode, $result);
        }

        return $result;
    }
}