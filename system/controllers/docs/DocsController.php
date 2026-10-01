<?php

require_once __DIR__ . '/DocsHelper.php';

class DocsController extends Controller {

    protected $controllerInfo = [
        'name' => LANG_CONTROLLER_DOCS_MANIFEST_NAME,
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => LANG_CONTROLLER_DOCS_MANIFEST_DESCRIPTION
    ];

    public function __construct($db) {
        parent::__construct($db);
        $this->initAdminBreadcrumbs();
    }

    public function getDefaultSettings() {
        return [
            'show_button_in_controllers' => true,
            'show_button_on_dashboard' => true
        ];
    }

    public function getSystemName() {
        return 'docs';
    }

    public function adminIndexAction() {
        $action = new \docs\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }

    public function adminViewAction() {
        $action = new \docs\actions\AdminView($this->db);
        $action->setController($this);
        return $action->execute();
    }

    public function adminApiAction() {
        $action = new \docs\actions\AdminApi($this->db);
        $action->setController($this);
        return $action->execute();
    }
}
