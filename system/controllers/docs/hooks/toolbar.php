<?php

/**
* Хук для внедрения кнопки "Документация" в контроллеры админ-панели
*/
\Event::listen('controller.render.content', function($content, $params = []) {

    $showButton = \SettingsHelper::get('controller_docs', 'show_button_in_controllers', true);
    if (!$showButton) {
        return $content;
    }

    $template = is_array($params) ? ($params['template'] ?? '') : (is_string($params) ? $params : '');
    if (strpos($template, 'admin/') !== 0) {
        return $content;
    }


    if (strpos($template, 'admin/docs/') === 0) {
        return $content;
    }

    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    $relativeUri = str_replace($basePath, '', $currentUri);
    $pathParts = explode('/', trim($relativeUri, '/'));

    $section = '';
    if (isset($pathParts[0]) && $pathParts[0] === 'admin') {
        $section = $pathParts[1] ?? '';
    }


    if (empty($section) || $section === 'login' || $section === 'logout') {
        return $content;
    }

    require_once dirname(__DIR__) . '/DocsHelper.php';
    $docFile = \docs\DocsHelper::getDocFileForSection($section);

    $btnTitle = LANG_DOCS_MENU_TITLE;
    $btnTooltip = LANG_DOCS_HEADER_BTN_TITLE;
    $iconHtml = function_exists('bloggy_icon') ? bloggy_icon('bs', 'book-half', '18', '#0d6efd', 'me-1') : '';

    $btnHtml = '<button type="button" class="btn btn-controller-docs" data-open-docs="true" data-docs-section="' . htmlspecialchars($section, ENT_QUOTES, 'UTF-8') . '" data-docs-file="' . htmlspecialchars($docFile, ENT_QUOTES, 'UTF-8') . '" title="' . htmlspecialchars($btnTooltip, ENT_QUOTES, 'UTF-8') . '">' . $iconHtml . '<span>' . $btnTitle . '</span></button>';

    $patternGap = '/(<div\s+class="d-flex(?:\s+btn-group)?\s+gap-2[^"]*">)([\s\S]*?)(<\/div>)/i';
    if (preg_match($patternGap, $content)) {
        $replaced = preg_replace_callback($patternGap, function($m) use ($btnHtml) {
            return $m[1] . "\n            " . $btnHtml . $m[2] . $m[3];
        }, $content, 1);
        return $replaced;
    }


    $patternHeader = '/(<div\s+class="d-flex(?:\s+btn-group)?\s+justify-content-between\s+align-items-center\s+mb-4">[\s\S]*?<h4[\s\S]*?<\/h4>\s*)([\s\S]*?)(<\/div>)/i';
    if (preg_match($patternHeader, $content)) {
        $replaced = preg_replace_callback($patternHeader, function($m) use ($btnHtml) {
            $right = trim($m[2]);
            if (empty($right)) {
                return $m[1] . '<div class="d-flex gap-2">' . $btnHtml . '</div>' . "\n    " . $m[3];
            } else {
                return $m[1] . '<div class="d-flex gap-2 align-items-center">' . $btnHtml . "\n" . $right . '</div>' . "\n    " . $m[3];
            }
        }, $content, 1);
        return $replaced;
    }

    return $content;
}, 50, 2);
