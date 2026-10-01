<?php

namespace docs\actions;

use docs\DocsHelper;

class AdminIndex extends DocsAction {

    public function execute() {
        $structure = DocsHelper::getStructure();
        $currentDoc = $_GET['doc'] ?? 'README.md';

        $docData = DocsHelper::loadDocument($currentDoc);

        $this->addBreadcrumb(LANG_CONTROLLER_DOCS_BREADCRUMB_DASHBOARD, ADMIN_URL);
        $this->addBreadcrumb(LANG_CONTROLLER_DOCS_BREADCRUMB_ROOT, ADMIN_URL . '/docs');
        if (!empty($docData['title']) && $currentDoc !== 'README.md') {
            $this->addBreadcrumb($docData['title']);
        }

        $this->render('admin/docs/index', [
            'structure'  => $structure,
            'currentDoc' => $docData['key'],
            'docData'    => $docData,
            'pageTitle'  => $docData['title'] . ' - ' . LANG_CONTROLLER_DOCS_PAGE_TITLE
        ]);
    }
}
