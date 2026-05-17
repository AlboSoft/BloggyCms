<?php

/**
 * Контроллер форм для публичного доступа
 */
class FormController extends Controller {
    
    private $formModel;
    
    public function __construct($db) {
        parent::__construct($db);
        $this->formModel = new FormModel($db);
    }

    /**
    * Возвращает системное имя контроллера
    * @return string
    */
    public function getSystemName() {
        return 'forms';
    }
    
    public function showAction($slug) {
        $action = new \forms\actions\ShowForm($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    public function processAction($slug) {
        $action = new \forms\actions\ProcessForm($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Вспомогательный метод для рендеринга формы
     * (может использоваться в шаблонах через shortcode или прямой вызов)
     */
    public function renderForm($slug) {
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            return LANG_CONTROLLER_FORM_NOT_FOUND_COMMENT;
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        $structure = $form['structure'] ?? [];
        
        ob_start();
        ?>
        <div class="form-container form-<?= html($slug) ?>">
            <?php if ($form['description']) { ?>
                <div class="form-description mb-4">
                    <?= nl2br(html($form['description'])) ?>
                </div>
            <?php } ?>
            
            <form action="<?= BASE_URL ?>/form/<?= html($slug) ?>/submit" 
                  method="POST" 
                  class="custom-form"
                  enctype="multipart/form-data"
                  id="form-<?= html($slug) ?>">
                  
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                
                <?php foreach ($structure as $field) { ?>
                    <?php if ($field['type'] === 'submit') { ?>
                        <div class="form-group mb-3">
                            <button type="submit" class="btn btn-primary">
                                <?= html($field['label'] ?? LANG_CONTROLLER_FORM_DEFAULT_SUBMIT_LABEL) ?>
                            </button>
                        </div>
                    <?php } elseif ($field['type'] !== 'hidden') { ?>
                        <div class="form-group mb-3">
                            <label for="field_<?= html($field['name']) ?>" class="form-label">
                                <?= html($field['label'] ?? '') ?>
                                <?php if (!empty($field['required'])) { ?>
                                    <span class="text-danger">*</span>
                                <?php } ?>
                            </label>
                            
                            <?php if (in_array($field['type'], ['text', 'email', 'tel', 'number', 'password'])) { ?>
                                <input type="<?= $field['type'] ?>"
                                    id="field_<?= html($field['name']) ?>"
                                    name="<?= html($field['name']) ?>"
                                    class="form-control"
                                    placeholder="<?= html($field['placeholder'] ?? '') ?>"
                                    value="<?= html($field['default_value'] ?? '') ?>"
                                    <?= !empty($field['required']) ? 'required' : '' ?>>
                                    
                            <?php } elseif ($field['type'] === 'textarea') { ?>
                                <textarea id="field_<?= html($field['name']) ?>"
                                    name="<?= html($field['name']) ?>"
                                    class="form-control"
                                    rows="<?= $field['rows'] ?? 4 ?>"
                                    placeholder="<?= html($field['placeholder'] ?? '') ?>"
                                    <?= !empty($field['required']) ? 'required' : '' ?>><?= html($field['default_value'] ?? '') ?></textarea>
                                    
                            <?php } elseif ($field['type'] === 'select') { ?>
                                <select id="field_<?= html($field['name']) ?>"
                                    name="<?= html($field['name']) ?>"
                                    class="form-select"
                                    <?= !empty($field['required']) ? 'required' : '' ?>>
                                    <option value=""><?= html($field['placeholder'] ?? LANG_CONTROLLER_FORM_SELECT_PLACEHOLDER) ?></option>
                                    <?php foreach ($field['options'] ?? [] as $option) { ?>
                                    <option value="<?= html($option['value'] ?? '') ?>">
                                        <?= html($option['label'] ?? '') ?>
                                    </option>
                                    <?php } ?>
                                </select>

                            <?php } elseif ($field['type'] === 'file') { ?>
                                <input type="file"
                                    id="field_<?= html($field['name']) ?>"
                                    name="<?= html($field['name']) . (!empty($field['multiple']) ? '[]' : '') ?>"
                                    class="form-control <?= html($field['class'] ?? '') ?>"
                                    <?= !empty($field['required']) ? 'required' : '' ?>
                                    <?= !empty($field['multiple']) ? 'multiple' : '' ?>
                                    <?= !empty($field['accept']) ? 'accept="' . html($field['accept']) . '"' : '' ?>>

                            <?php } ?>
                            
                            <?php if (!empty($field['description'])) { ?>
                                <div class="form-text"><?= html($field['description']) ?></div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <input type="hidden" 
                               name="<?= html($field['name']) ?>" 
                               value="<?= html($field['default_value'] ?? '') ?>">
                    <?php } ?>
                <?php } ?>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }
}