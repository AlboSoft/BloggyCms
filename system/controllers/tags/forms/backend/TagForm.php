<?php

/**
* Форма для создания и редактирования тегов
*/
class TagForm extends AdminForm {
    
    /**
    * Возвращает заголовок формы
    * @return string
    */
    public function getTitle(): string {
        return LANG_TAGFORM_TITLE;
    }
    
    /**
    * Возвращает маппинг полей для сохранения
    * @return array
    */
    public function getFieldMapping(): array {
        return [
            'name' => [
                'storage' => 'field',
                'db_field' => 'name'
            ],
            'description' => [
                'storage' => 'field',
                'db_field' => 'description'
            ],
            'image' => [
                'storage' => 'field',
                'db_field' => 'image'
            ]
        ];
    }
    
    /**
    * Возвращает Fieldset для рендеринга формы
    * @return array
    */
    public function getFieldsets(): array {
        $fieldsets = [];
        
        $fieldsets[] = new Fieldset(LANG_TAGFORM_MAIN_SECTION, [
            'icon' => 'bi bi-info-circle',
            'columns' => 'custom',
            'fields' => [
                FieldFactory::string('name', [
                    'title' => LANG_TAGFORM_FIELD_NAME,
                    'required' => true,
                    'maxlength' => 50,
                    'column' => '12',
                    'placeholder' => LANG_TAGFORM_FIELD_NAME_PLACEHOLDER,
                    'storage' => 'field',
                    'db_field' => 'name'
                ]),
                
                FieldFactory::textarea('description', [
                    'title' => LANG_TAGFORM_FIELD_DESCRIPTION,
                    'rows' => 5,
                    'column' => '12',
                    'placeholder' => LANG_TAGFORM_FIELD_DESCRIPTION_PLACEHOLDER,
                    'storage' => 'field',
                    'db_field' => 'description'
                ]),
                
                FieldFactory::image('image', [
                    'title' => LANG_TAGFORM_FIELD_IMAGE,
                    'upload_path' => 'uploads/tags/',
                    'column' => '6',
                    'hint' => LANG_TAGFORM_FIELD_IMAGE_HINT,
                    'storage' => 'field',
                    'db_field' => 'image'
                ])
            ]
        ]);
        
        return $fieldsets;
    }
    
    /**
    * Подготавливает данные перед сохранением
    * @param array $postData Данные из $_POST
    * @param array $files Данные из $_FILES
    * @return array
    */
    public function prepareData(array $postData, array $files): array {
        $data = parent::prepareData($postData, $files);
        
        $settings = $postData['settings'] ?? [];
        
        if (isset($settings['name'])) {
            $data['fields']['name'] = trim($settings['name']);
        }
        if (isset($settings['description'])) {
            $data['fields']['description'] = trim($settings['description']);
        }
        
        if (isset($files['image_file']) && $files['image_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $uploadDir = UPLOADS_PATH . '/tags/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $file = $files['image_file'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                $fileType = mime_content_type($file['tmp_name']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    $this->addError('image', LANG_TAGFORM_IMAGE_UPLOAD_ERROR_TYPE);
                    return $data;
                }
                
                if ($file['size'] > 2 * 1024 * 1024) {
                    $this->addError('image', LANG_TAGFORM_IMAGE_UPLOAD_ERROR_SIZE);
                    return $data;
                }
                
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $slugName = $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME));
                $fileName = uniqid() . '_' . $slugName . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    $this->addError('image', LANG_TAGFORM_IMAGE_UPLOAD_ERROR_SAVE);
                    return $data;
                }
                
                $data['fields']['image'] = $fileName;
                
            } catch (\Exception $e) {
                $this->addError('image', $e->getMessage());
            }
        }
        
        if (isset($postData['remove_image']) && $postData['remove_image'] == 1) {
            if (isset($postData['current_image']) && !empty($postData['current_image'])) {
                $oldImagePath = UPLOADS_PATH . '/tags/' . $postData['current_image'];
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $data['fields']['image'] = null;
        }
        
        return $data;
    }
    
    /**
    * Валидация данных
    */
    public function validate(): void {
        $settings = $this->rawData['settings'] ?? [];
        $name = trim($settings['name'] ?? '');
        
        if (empty($name)) {
            $this->addError('name', LANG_TAGFORM_FIELD_NAME_REQUIRED);
        }
        
        if (!empty($name) && mb_strlen($name) > 50) {
            $this->addError('name', LANG_TAGFORM_FIELD_NAME_MAXLENGTH);
        }
    }
    
    /**
    * Заполняет форму данными из БД для редактирования
    * @param array $data Данные тега из БД
    * @return array Данные для рендеринга формы
    */
    public function populateFromDb(array $data): array {
        $formData = [];
        
        $formData['name'] = $data['name'] ?? '';
        $formData['description'] = $data['description'] ?? '';
        $formData['image'] = $data['image'] ?? '';
        
        if (!empty($data['image'])) {
            $formData['current_image'] = $data['image'];
        }
        
        return $formData;
    }
    
    /**
    * Генерирует slug из строки для имени файла
    * @param string $string Исходная строка
    * @return string Очищенная строка
    */
    private function generateSlug($string) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        
        $string = strtr(mb_strtolower($string), $converter);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }
}