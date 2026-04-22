<?php

namespace html_blocks\actions;

/**
* Действие редактирования HTML-блока в админ-панели
* @package html_blocks\actions
*/
class AdminEdit extends HtmlBlockAction {
    
    /**
    * Метод выполнения редактирования HTML-блока
    * @return void
    */
    public function execute() {
        
        try {
            $block = $this->htmlBlockModel->getById($this->id);
        
            if (!$block) {
                \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_BLOCK_NOT_FOUND);
                $this->redirect(ADMIN_URL . '/html-blocks');
                return;
            }

            $blockTypeName = $block['block_type'] ?? 'DefaultBlock';

            $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_BREADCRUMB_DASHBOARD, ADMIN_URL);
            $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_BREADCRUMB_BLOCKS, ADMIN_URL . '/html-blocks');
            $this->addBreadcrumb(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_BREADCRUMB_EDIT . html($block['name']));

            if ($blockTypeName !== 'DefaultBlock' && !$this->blockTypeManager->isBlockTypeActive($blockTypeName)) {
                \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_BLOCK_TYPE_DISABLED);
                $this->redirect(ADMIN_URL . '/html-blocks');
                return;
            }

            if ($blockTypeName !== 'DefaultBlock') {
                $this->blockTypeManager->loadBlockAssets($blockTypeName);
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST' || !empty($_FILES)) {
                try {
                    if (empty($_POST['name']) || empty($_POST['slug'])) {
                        \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_NAME_SLUG_REQUIRED);
                        $this->renderFormWithData($_POST, $blockTypeName, $block);
                        return;
                    }

                    $typeId = null;
                    $settings = [];
                    
                    if ($blockTypeName !== 'DefaultBlock') {
                        $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
                        if ($blockType) {
                            $typeId = $blockType['id'];
                            $blockInstance = $blockType['class'];
                            $settings = $_POST['settings'] ?? [];
                            
                            list($isValid, $errors) = $blockInstance->validateSettings($settings);
                            if (!$isValid) {
                                \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_SETTINGS_ERROR . implode(', ', $errors));
                                $this->renderFormWithData($_POST, $blockTypeName, $block);
                                return;
                            }
                            
                            $settings = $blockInstance->prepareSettings($settings);
                        }
                    } else {
                            $settings = [
                                'html' => $_POST['settings']['html'] ?? '',
                                'use_fragment' => isset($_POST['settings']['use_fragment']) ? 1 : 0,
                                'selected_fragment' => $_POST['settings']['selected_fragment'] ?? ''
                            ];
                    }

                    $cssFiles = $this->processAssetFiles($_POST['css_files'] ?? []);
                    $jsFiles = $this->processAssetFiles($_POST['js_files'] ?? []);
                    
                    if ($blockTypeName !== 'DefaultBlock' && isset($blockInstance)) {
                        $systemCss = $blockInstance->getSystemCss();
                        $systemJs = $blockInstance->getSystemJs();
                        
                        $cssFiles = array_merge($systemCss, $cssFiles);
                        $jsFiles = array_merge($systemJs, $jsFiles);
                    }

                    $data = [
                        'name' => $_POST['name'],
                        'slug' => $_POST['slug'],
                        'content' => '',
                        'type_id' => $typeId,
                        'settings' => $settings,
                        'css_files' => $cssFiles,
                        'js_files' => $jsFiles,
                        'inline_css' => $_POST['inline_css'] ?? '',
                        'inline_js' => $_POST['inline_js'] ?? '',
                        'template' => $_POST['template'] ?? ($block['template'] ?? 'default')
                    ];
                    
                    $result = $this->htmlBlockModel->update($this->id, $data);

                    \Event::trigger('html_block.saved', ['id' => $this->id, 'action' => 'update']);
                    
                    \Notification::success(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_SUCCESS);
                    
                    $this->redirect(ADMIN_URL . '/html-blocks');
                    
                } catch (\Exception $e) {
                    \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_ERROR . $e->getMessage());
                    $this->renderFormWithData($_POST, $blockTypeName, $block);
                }
            } 
            else {
                $this->renderForm($block, $blockTypeName);
            }
            
        } catch (\Exception $e) {
            \Notification::error(LANG_ACTION_HTMLBLOCKS_ADMINEDIT_LOAD_ERROR);
            $this->redirect(ADMIN_URL . '/html-blocks');
        }
    }
}