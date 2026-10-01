<?php

namespace docs\actions;

use docs\DocsHelper;

class AdminView extends DocsAction {

    public function execute() {
        $doc = $_GET['doc'] ?? 'README.md';
        $section = $_GET['section'] ?? '';

        if (!empty($section)) {
            $doc = DocsHelper::getDocFileForSection($section);
        }

        $this->redirect(ADMIN_URL . '/docs?doc=' . rawurlencode($doc));
    }
}
