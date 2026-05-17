<?php

/**
* Контроллер управления формами в админ-панели
*/
class AdminFormController extends Controller {
    
    private $formModel;
    
    /**
    * Конструктор контроллера форм
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->formModel = new FormModel($db);
        
        if (!isset($_SESSION['user_id'])) {
            \Notification::error(LANG_CONTROLLER_ADMINFORM_AUTH_REQUIRED);
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            \Notification::error(LANG_CONTROLLER_ADMINFORM_ACCESS_DENIED);
            $this->redirect(BASE_URL);
            return;
        }
    }

    /**
    * Возвращает системное имя контроллера
    * @return string
    */
    public function getSystemName() {
        return 'forms';
    }
    
    /**
    * Действие: Главная страница управления формами
    * @return mixed
    */
    public function adminIndexAction() {
        $action = new \forms\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Создание новой формы
    * @return mixed
    */
    public function createAction() {
        $action = new \forms\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Редактирование существующей формы
    * @param int $id ID редактируемой формы
    * @return mixed
    */
    public function editAction($id) {
        $action = new \forms\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление формы
    * @param int $id ID удаляемой формы
    * @return mixed
    */
    public function deleteAction($id) {
        $action = new \forms\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Получение структуры формы через AJAX
    * @param int $id ID формы
    * @return mixed JSON-ответ со структурой формы
    */
    public function getStructureAction($id) {
        $action = new \forms\actions\AdminGetStructure($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Предварительный просмотр формы
    * @param int $id ID формы для предпросмотра
    * @return mixed
    */
    public function previewAction($id) {
        $action = new \forms\actions\AdminPreview($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Настройки формы
    * @param int $id ID формы
    * @return mixed
    */
    public function settingsAction($id) {
        $action = new \forms\actions\AdminSettings($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Переключение статуса формы 
    * @param int $id ID формы
    * @return mixed
    */
    public function toggleStatusAction($id) {
        $action = new \forms\actions\AdminToggleStatus($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Просмотр отправок формы
    * @param int $id ID формы
    * @return mixed
    */
    public function showAction($id) {
        $action = new \forms\actions\AdminShow($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Обработка отправки формы (публичная)
    * @param string $slug Слаг формы
    * @return mixed
    */
    public function submitAction($slug) {
        $action = new \forms\actions\FormSubmit($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Просмотр формы (публичная)
    * @param string $slug Слаг формы
    * @return mixed
    */
    public function viewAction($slug) {
        $action = new \forms\actions\FormView($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие: Экспорт отправок в CSV
    * @param int $id ID формы
    * @return mixed
    */
    public function exportAction($id) {
        $action = new \forms\actions\AdminExport($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Получение отправки через AJAX
    * @param int $id ID отправки
    * @return mixed
    */
    public function getSubmissionAction($id) {
        $action = new \forms\actions\AdminGetSubmission($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление отправки
    * @param int $id ID отправки
    * @return mixed
    */
    public function deleteSubmissionAction($id) {
        $action = new \forms\actions\AdminDeleteSubmission($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Обновление статуса отправки
    * @param int $id ID отправки
    * @return mixed
    */
    public function updateSubmissionStatusAction($id) {
        $action = new \forms\actions\AdminUpdateSubmissionStatus($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие: Удаление всех отправок формы
    * @param int $id ID формы
    * @return mixed
    */
    public function deleteAllSubmissionsAction($id) {
        $action = new \forms\actions\AdminDeleteAllSubmissions($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Получение доступных шаблонов форм
    */
    public function getAvailableTemplates() {
        return $this->formModel->getAvailableTemplates();
    }

    /**
    * Получение текущей темы
    */
    public function getCurrentTheme() {
        if (defined('DEFAULT_TEMPLATE')) {
            return DEFAULT_TEMPLATE;
        }
        
        if (class_exists('SettingsHelper') && method_exists('SettingsHelper', 'get')) {
            try {
                $theme = SettingsHelper::get('site', 'site_template');
                if (empty($theme)) {
                    $theme = SettingsHelper::get('site', 'theme');
                }
                return !empty($theme) ? $theme : 'default';
            } catch (Exception $e) {
                return 'default';
            }
        }
        
        return 'default';
    }
    
    /**
    * Рендеринг одного поля формы для конструктора
    * @param array $field Данные поля
    * @param string $index Уникальный индекс поля в структуре
    * @return string HTML-код поля
    */
    public function renderFormField($field, $index) {
        $fieldType = $field['type'] ?? 'text';
        $fieldLabel = html($field['label'] ?? '');
        $fieldName = html($field['name'] ?? '');
        $fieldValue = html($field['default_value'] ?? '');
        $placeholder = html($field['placeholder'] ?? '');
        $required = !empty($field['required']);
        $description = html($field['description'] ?? '');
        $options = $field['options'] ?? [];
        $validation = $field['validation'] ?? [];
        
        ob_start();
        ?>
        <div class="form-field card mb-2" data-index="<?= $index ?>" data-type="<?= $fieldType ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-<?= $this->getFieldTypeIcon($fieldType) ?> me-2"></i>
                        <?= $this->getFieldTypeLabel($fieldType) ?>
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary form-field-handle">
                            <i class="bi bi-arrows-move"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary edit-form-field" 
                                data-bs-toggle="modal" 
                                data-bs-target="#fieldSettingsModal">
                            <i class="bi bi-gear"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger remove-form-field">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2"><?= strtoupper($fieldType) ?></span>
                            <strong><?= $fieldLabel ?: LANG_CONTROLLER_ADMINFORM_NO_TITLE ?></strong>
                            <?php if ($required): ?>
                                <span class="badge bg-danger ms-2"><?php echo LANG_CONTROLLER_ADMINFORM_REQUIRED_BADGE; ?></span>
                            <?php endif; ?>
                            <?php if (!empty($fieldName)): ?>
                                <small class="text-muted ms-2">(name: <?= $fieldName ?>)</small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($description): ?>
                            <p class="small text-muted mb-2"><?= $description ?></p>
                        <?php endif; ?>
                        
                        <div class="preview-field mt-2">
                            <?= $this->renderFieldPreview($field) ?>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" class="field-type" value="<?= $fieldType ?>">
                <input type="hidden" class="field-label" value="<?= $fieldLabel ?>">
                <input type="hidden" class="field-name" value="<?= $fieldName ?>">
                <input type="hidden" class="field-default-value" value="<?= $fieldValue ?>">
                <input type="hidden" class="field-placeholder" value="<?= $placeholder ?>">
                <input type="hidden" class="field-required" value="<?= $required ? '1' : '0' ?>">
                <input type="hidden" class="field-description" value="<?= $description ?>">
                <input type="hidden" class="field-options" value="<?= html(json_encode($options)) ?>">
                <input type="hidden" class="field-validation" value="<?= html(json_encode($validation)) ?>">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Получение иконки для типа поля
    */
    private function getFieldTypeIcon($type) {
        $icons = [
            'text' => 'input-cursor-text',
            'textarea' => 'textarea-t',
            'email' => 'envelope',
            'tel' => 'telephone',
            'number' => '123',
            'date' => 'calendar',
            'select' => 'menu-down',
            'checkbox' => 'check-square',
            'radio' => 'circle',
            'file' => 'paperclip',
            'password' => 'key',
            'hidden' => 'eye-slash',
            'submit' => 'send'
        ];
        
        return $icons[$type] ?? 'input-cursor';
    }
    
    /**
    * Получение читаемого названия типа поля
    */
    private function getFieldTypeLabel($type) {
        $labels = [
            'text' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_TEXT,
            'textarea' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_TEXTAREA,
            'email' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_EMAIL,
            'tel' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_TEL,
            'number' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_NUMBER,
            'date' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_DATE,
            'select' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_SELECT,
            'checkbox' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_CHECKBOX,
            'radio' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_RADIO,
            'file' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_FILE,
            'password' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_PASSWORD,
            'hidden' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_HIDDEN,
            'submit' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_SUBMIT
        ];
        
        return $labels[$type] ?? LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_UNKNOWN;
    }
    
    /**
    * Рендеринг превью поля
    */
    private function renderFieldPreview($field) {
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? '';
        $name = $field['name'] ?? '';
        $value = $field['default_value'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $required = !empty($field['required']);
        $options = $field['options'] ?? [];
        
        ob_start();
        ?>
        <div class="form-preview">
            <?php switch ($type) {
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'date':
                case 'password': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= html($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="<?= $type ?>" 
                            class="form-control form-control-sm" 
                            value="<?= html($value) ?>"
                            placeholder="<?= html($placeholder) ?>"
                            disabled>
                    </div>
                    <?php break;
                
                case 'textarea': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= html($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <textarea class="form-control form-control-sm" 
                                placeholder="<?= html($placeholder) ?>"
                                rows="3"
                                disabled><?= html($value) ?></textarea>
                    </div>
                    <?php break;
                
                case 'select': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= html($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <select class="form-select form-select-sm" disabled>
                            <option value=""><?= html($placeholder) ?></option>
                            <?php foreach ($options as $option) { ?>
                                <option value="<?= html($option['value'] ?? '') ?>"
                                        <?= ($value === ($option['value'] ?? '')) ? 'selected' : '' ?>>
                                    <?= html($option['label'] ?? '') ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <?php break;
                
                case 'checkbox': ?>
                    <div class="mb-2">
                        <div class="form-check">
                            <input type="checkbox" 
                                class="form-check-input" 
                                <?= !empty($value) ? 'checked' : '' ?>
                                disabled>
                            <label class="form-check-label small"><?= html($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        </div>
                    </div>
                    <?php break;
                
                case 'radio': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= html($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <?php foreach ($options as $option) { ?>
                            <div class="form-check">
                                <input type="radio" 
                                    class="form-check-input" 
                                    name="preview_<?= $name ?>"
                                    <?= ($value === ($option['value'] ?? '')) ? 'selected' : '' ?>
                                    disabled>
                                <label class="form-check-label small"><?= html($option['label'] ?? '') ?></label>
                            </div>
                        <?php } ?>
                    </div>
                    <?php break;

                case 'file': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= html($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="file"
                            class="form-control form-control-sm"
                            <?= !empty($field['multiple']) ? 'multiple' : '' ?>
                            <?= !empty($field['accept']) ? 'accept="' . html($field['accept']) . '"' : '' ?>
                            disabled>
                        <?php if (!empty($field['description'])) { ?>
                            <div class="form-text small"><?= html($field['description']) ?></div>
                        <?php } ?>
                    </div>
                    <?php break;
                
                case 'submit': ?>
                    <div class="mb-2">
                        <button type="button" class="btn btn-primary btn-sm" disabled>
                            <?= html($label) ?>
                        </button>
                    </div>
                    <?php break;
                
                default: ?>
                    <div class="alert alert-warning small p-2">
                        <?php echo LANG_CONTROLLER_ADMINFORM_PREVIEW_UNAVAILABLE . $type; ?>
                    </div>
            <?php } ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
    * Получение всех доступных типов полей
    */
    public function getAvailableFieldTypes() {
        return [
            'text' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_TEXT,
                'icon' => 'input-cursor-text',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'textarea' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_TEXTAREA,
                'icon' => 'textarea-t',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'email' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_EMAIL,
                'icon' => 'envelope',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'tel' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_TEL,
                'icon' => 'telephone',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'number' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_NUMBER,
                'icon' => '123',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'date' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_DATE,
                'icon' => 'calendar',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'select' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_SELECT,
                'icon' => 'menu-down',
                'has_options' => true,
                'has_placeholder' => true
            ],
            'checkbox' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_CHECKBOX,
                'icon' => 'check-square',
                'has_options' => false,
                'has_placeholder' => false
            ],
            'radio' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_RADIO,
                'icon' => 'circle',
                'has_options' => true,
                'has_placeholder' => false
            ],
            'file' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_FILE,
                'icon' => 'paperclip',
                'has_options' => false,
                'has_placeholder' => false
            ],
            'password' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_PASSWORD,
                'icon' => 'key',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'hidden' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_HIDDEN,
                'icon' => 'eye-slash',
                'has_options' => false,
                'has_placeholder' => false
            ],
            'submit' => [
                'label' => LANG_CONTROLLER_ADMINFORM_FIELD_TYPE_SUBMIT,
                'icon' => 'send',
                'has_options' => false,
                'has_placeholder' => false
            ]
        ];
    }
    
    /**
    * Получение типов валидации
    */
    public function getValidationTypes() {
        return [
            'required' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_REQUIRED_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_REQUIRED_DESC
            ],
            'email' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_EMAIL_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_EMAIL_DESC
            ],
            'url' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_URL_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_URL_DESC
            ],
            'numeric' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_NUMERIC_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_NUMERIC_DESC
            ],
            'min' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_MIN_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_MIN_DESC,
                'has_param' => true
            ],
            'max' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_MAX_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_MAX_DESC,
                'has_param' => true
            ],
            'regex' => [
                'label' => LANG_CONTROLLER_ADMINFORM_VALIDATION_REGEX_LABEL,
                'description' => LANG_CONTROLLER_ADMINFORM_VALIDATION_REGEX_DESC,
                'has_param' => true
            ]
        ];
    }

    /**
    * Действие: Генерация примера капчи через AJAX
    * @return mixed JSON-ответ с примером капчи
    */
    public function generateCaptchaExampleAction() {
        header('Content-Type: application/json');
        
        $type = $_POST['type'] ?? 'math';
        $question = $_POST['question'] ?? '';
        
        $captchaExample = $this->generateCaptchaExample($type, $question);
        
        echo json_encode([
            'success' => true,
            'question' => $captchaExample['question'],
            'answer' => $captchaExample['answer']
        ]);
        exit;
    }

    /**
    * Генерация примера для капчи (для AJAX)
    */
    private function generateCaptchaExample($type = 'math', $customQuestion = '') {
        switch ($type) {
            case 'math':
                if (!empty($customQuestion) && preg_match('/(\d+)\s*([+\-*\/])\s*(\d+)/', $customQuestion, $matches)) {
                    $a = intval($matches[1]);
                    $b = intval($matches[3]);
                    $op = $matches[2];
                    $question = $customQuestion;
                    $answer = eval("return $a $op $b;");
                } else {
                    $operations = ['+', '-', '*'];
                    $op = $operations[array_rand($operations)];
                    $a = rand(1, 10);
                    $b = rand(1, 10);
                    if ($op === '-') {
                        $a = max($a, $b) + rand(0, 5);
                    }
                    $question = LANG_CONTROLLER_ADMINFORM_CAPTCHA_MATH_QUESTION . "$a $op $b?";
                    $answer = eval("return $a $op $b;");
                }
                break;
                
            case 'text':
                if (!empty($customQuestion)) {
                    $question = $customQuestion;
                    $answer = '';
                } else {
                    $questions = [
                        LANG_CONTROLLER_ADMINFORM_CAPTCHA_TEXT_QUESTION_1 => LANG_CONTROLLER_ADMINFORM_CAPTCHA_TEXT_ANSWER_1,
                        LANG_CONTROLLER_ADMINFORM_CAPTCHA_TEXT_QUESTION_2 => LANG_CONTROLLER_ADMINFORM_CAPTCHA_TEXT_ANSWER_2,
                        LANG_CONTROLLER_ADMINFORM_CAPTCHA_TEXT_QUESTION_3 => LANG_CONTROLLER_ADMINFORM_CAPTCHA_TEXT_ANSWER_3
                    ];
                    $question = array_rand($questions);
                    $answer = $questions[$question];
                }
                break;
                
            case 'logic':
                if (!empty($customQuestion)) {
                    $question = $customQuestion;
                    $answer = '';
                } else {
                    $questions = [
                        LANG_CONTROLLER_ADMINFORM_CAPTCHA_LOGIC_QUESTION_1 => LANG_CONTROLLER_ADMINFORM_CAPTCHA_LOGIC_ANSWER_1,
                        LANG_CONTROLLER_ADMINFORM_CAPTCHA_LOGIC_QUESTION_2 => LANG_CONTROLLER_ADMINFORM_CAPTCHA_LOGIC_ANSWER_2,
                        LANG_CONTROLLER_ADMINFORM_CAPTCHA_LOGIC_QUESTION_3 => LANG_CONTROLLER_ADMINFORM_CAPTCHA_LOGIC_ANSWER_3
                    ];
                    $question = array_rand($questions);
                    $answer = $questions[$question];
                }
                break;
                
            case 'image':
                $question = LANG_CONTROLLER_ADMINFORM_CAPTCHA_IMAGE_QUESTION;
                $answer = 'ABCD';
                break;
                
            default:
                $question = LANG_CONTROLLER_ADMINFORM_CAPTCHA_DEFAULT_QUESTION;
                $answer = '4';
        }
        
        return [
            'question' => $question,
            'answer' => $answer
        ];
    }

}