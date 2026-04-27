<?php

if (!function_exists('render_simple_form')) {
    /**
    * Рендерит админскую CRUD-форму на основе AdminForm
    * @param AdminForm|string $form Экземпляр формы или имя класса
    * @param array $currentData Текущие данные для заполнения формы
    * @param array $options Дополнительные опции
    * @return string HTML формы
    */
    function render_simple_form($form, array $currentData = [], array $options = []) {
        if (is_string($form)) {
            $db = Database::getInstance();
            $form = new $form($db);
        }
        
        if (!($form instanceof AdminForm)) {
            return '<!-- Invalid form object. Expected AdminForm instance. -->';
        }
        
        $options = array_merge([
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
            'class' => '',
            'id' => '',
            'submit_text' => LANG_HELPER_SIMPLE_FORM_SUBMIT,
            'cancel_url' => null,
            'cancel_text' => LANG_HELPER_SIMPLE_FORM_CANCEL
        ], $options);
        
        $formId = $options['id'] ?: 'form_' . md5(get_class($form));
        
        ob_start();
        ?>
        <form method="<?php echo htmlspecialchars($options['method']); ?>" 
              enctype="<?php echo htmlspecialchars($options['enctype']); ?>" 
              class="<?php echo htmlspecialchars($options['class']); ?>"
              id="<?php echo htmlspecialchars($formId); ?>">
            
            <input type="hidden" name="simple_csrf" value="<?php echo md5(session_id()); ?>">
            
            <?php echo $form->render($currentData); ?>
            
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <?php echo htmlspecialchars($options['submit_text']); ?>
                </button>
                <?php if ($options['cancel_url']) { ?>
                <a href="<?php echo htmlspecialchars($options['cancel_url']); ?>" class="btn btn-outline-secondary">
                    <?php echo htmlspecialchars($options['cancel_text']); ?>
                </a>
                <?php } ?>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
}