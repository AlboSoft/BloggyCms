<?php

namespace fragments\actions;

/**
* Действие отображения отдельной записи фрагмента
* @package fragments\actions
*/
class Entry extends FragmentAction {
    
    /**
    * Отображение записи по адресу /f-{systemName}/{slug}.html
    * @return void
    */
    public function execute() {
        $systemName = $this->params['systemName'] ?? null;
        $slug = $this->params['slug'] ?? null;
        
        if (!$systemName || !$slug) {
            $this->notFound();
            return;
        }
        
        $slug = preg_replace('/\.html?$/i', '', (string)$slug);
        $slug = trim($slug, '/');
        
        $fragment = $this->fragmentModel->getBySystemName($systemName);
        
        if (!$fragment || $fragment['status'] !== 'active' || empty($fragment['entry_template_enabled'])) {
            $this->notFound();
            return;
        }
        
        $entry = $this->entryModel->getBySlug($fragment['id'], $slug);
        
        if (!$entry) {
            $this->entryModel->ensureSlugs($fragment['id']);
            $entry = $this->entryModel->getBySlug($fragment['id'], $slug);
        }
        
        if (!$entry || $entry['status'] !== 'active') {
            $this->notFound();
            return;
        }
        
        $fields = $this->fragmentModel->getFields($fragment['id']);
        $entryTitle = $this->resolveEntryTitle($fragment, $entry, $fields);
        
        $this->loadFragmentAssets($fragment);
        
        $this->addBreadcrumb(LANG_ACTION_FRAGMENTS_ENTRY_BREADCRUMB_HOME, BASE_URL);
        $this->addBreadcrumb($fragment['name']);
        $this->addBreadcrumb($entryTitle);
        $this->setPageTitle($entryTitle);
        
        $this->render('front/fragments/entry', [
            'fragment' => $fragment,
            'entry' => $entry,
            'fields' => $fields,
            'pageTitle' => $entryTitle,
            'entryTitle' => $entryTitle,
            'entryContent' => \FragmentHelper::renderEntryContent($fragment, $entry, $fields)
        ]);
    }
    
    /**
    * Заголовок записи: значение первого непустого текстового поля
    * @param array $fragment
    * @param array $entry
    * @param array $fields
    * @return string
    */
    private function resolveEntryTitle($fragment, $entry, $fields) {
        foreach ($fields as $field) {
            if (!in_array($field['type'], ['string', 'text'], true)) {
                continue;
            }
            
            $value = $entry['data'][$field['system_name']] ?? '';
            
            if (!is_scalar($value)) {
                continue;
            }
            
            $value = trim(strip_tags((string)$value));
            
            if ($value !== '') {
                return mb_substr($value, 0, 200, 'UTF-8');
            }
        }
        
        return $fragment['name'];
    }
}
