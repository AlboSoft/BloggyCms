<?php

/**
* Публичный контроллер для рендеринга фрагментов
*/
class FragmentController extends Controller {
    
    /**
    * @var FragmentModel
    */
    private $fragmentModel;
    
    /**
    * @var FragmentEntryModel
    */
    private $entryModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->fragmentModel = new FragmentModel($db);
        $this->entryModel = new FragmentEntryModel($db);
    }
    
    /**
    * Отображение фрагмента по системному имени
    * 
    * @param string $systemName
    */
    public function showAction($systemName) {
        $fragment = $this->fragmentModel->getBySystemName($systemName);
        
        if (!$fragment || $fragment['status'] !== 'active') {
            echo sprintf(LANG_CONTROLLER_FRAGMENT_NOT_FOUND_COMMENT, html($systemName));
            return;
        }
        
        $this->loadFragmentAssets($fragment);
        
        $entries = $this->entryModel->getByFragment($fragment['id']);
        $fields = $this->fragmentModel->getFields($fragment['id']);
        
        $this->renderFragment($fragment, $entries, $fields);
    }
    
    /**
    * Загрузка ресурсов фрагмента
    * @param array $fragment
    */
    private function loadFragmentAssets($fragment) {
        if (!empty($fragment['css_files'])) {
            foreach ($fragment['css_files'] as $cssFile) {
                if (!empty($cssFile)) {
                    front_css($cssFile);
                }
            }
        }
        
        if (!empty($fragment['js_files'])) {
            foreach ($fragment['js_files'] as $jsFile) {
                if (!empty($jsFile)) {
                    front_js($jsFile);
                }
            }
        }
        
        if (!empty($fragment['inline_css'])) {
            front_inline_css($fragment['inline_css']);
        }
        
        if (!empty($fragment['inline_js'])) {
            front_inline_js($fragment['inline_js']);
        }
    }
    
    /**
    * Рендеринг фрагмента 
    * @param array $fragment
    * @param array $entries
    * @param array $fields
    */
    private function renderFragment($fragment, $entries, $fields) {
        $templateFile = $this->findFragmentTemplate($fragment['system_name']);
        
        if ($templateFile && file_exists($templateFile)) {
            extract(['fragment' => $fragment, 'entries' => $entries, 'fields' => $fields]);
            include $templateFile;
        } else {
            $this->renderDefault($fragment, $entries, $fields);
        }
    }
    
    /**
    * Поиск шаблона фрагмента в теме
    * @param string $systemName
    * @return string|null
    */
    private function findFragmentTemplate($systemName) {
        $currentTheme = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        
        $paths = [
            BASE_PATH . "/templates/{$currentTheme}/front/fragments/{$systemName}.php",
            BASE_PATH . "/templates/default/front/fragments/{$systemName}.php"
        ];
        
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
    * Стандартный рендеринг фрагмента 
    * @param array $fragment
    * @param array $entries
    * @param array $fields
    */
    private function renderDefault($fragment, $entries, $fields) {
        ?>
        <div class="fragment fragment-<?php echo html($fragment['system_name']); ?>">
            <?php if (!empty($fragment['description'])) { ?>
                <div class="fragment-description">
                    <?php echo html($fragment['description']); ?>
                </div>
            <?php } ?>
            
            <?php if (empty($entries)) { ?>
                <div class="fragment-empty">
                    <p><?php echo LANG_CONTROLLER_FRAGMENT_NO_ENTRIES; ?></p>
                </div>
            <?php } else { ?>
                <div class="fragment-entries">
                    <?php foreach ($entries as $entry) { ?>
                        <div class="fragment-entry">
                            <?php foreach ($fields as $field) { ?>
                                <?php $value = $entry['data'][$field['system_name']] ?? null; ?>
                                <?php if ($value !== null && $value !== '') { ?>
                                    <div class="fragment-field fragment-field-<?php echo html($field['system_name']); ?>">
                                        <div class="field-label"><?php echo html($field['name']); ?>:</div>
                                        <div class="field-value">
                                            <?php echo $this->renderFieldValue($field, $value); ?>
                                        </div>
                                    </div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
        <?php
    }
    
    /**
    * Рендеринг значения поля
    * @param array $field
    * @param mixed $value
    * @return string
    */
    private function renderFieldValue($field, $value) {
        $fieldManager = new FieldManager($this->db);
        
        return $fieldManager->renderFieldDisplay(
            $field['type'],
            $value,
            $field['config'],
            'fragment_entry',
            0
        );
    }
}