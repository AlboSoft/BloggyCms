<?php

namespace docs\actions;

use docs\DocsHelper;

class AdminApi extends DocsAction {

    public function execute() {
        header('Content-Type: application/json; charset=utf-8');

        $section = $_GET['section'] ?? '';
        $doc = $_GET['doc'] ?? '';

        if (!empty($section)) {
            $doc = DocsHelper::getDocFileForSection($section);
        }

        if (empty($doc)) {
            $doc = 'README.md';
        }

        $docData = DocsHelper::loadDocument($doc);

        echo json_encode([
            'success'   => $docData['success'],
            'title'     => $docData['title'],
            'html'      => $docData['html'],
            'doc'       => $docData['key'],
            'full_url'  => ADMIN_URL . '/docs?doc=' . rawurlencode($docData['key'])
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
