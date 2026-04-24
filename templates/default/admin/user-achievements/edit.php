<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'pencil-square', '24', '#000', 'me-2'); ?>
            <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_TITLE; ?>
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/user-achievements" class="btn btn-outline-secondary btn-sm">
                <?php echo bloggy_icon('bs', 'arrow-left', '18', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_BACK_BTN; ?>
            </a>
            <a href="<?php echo ADMIN_URL; ?>/user-achievements/delete/<?php echo $achievement['id']; ?>" 
               class="btn btn-outline-danger btn-sm"
               onclick="return confirm('<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_DELETE_CONFIRM; ?>')">
                <?php echo bloggy_icon('bs', 'trash', '18', '#000', 'me-1'); ?> <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_DELETE_BTN; ?>
            </a>
        </div>
    </div>
    
    <form method="post" enctype="multipart/form-data" id="achievementForm">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_NAME_LABEL; ?>
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" 
                                value="<?php echo html($achievement['name'] ?? ''); ?>" 
                                required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_DESCRIPTION_LABEL; ?></label>
                            <textarea class="form-control" name="description" rows="3"
                                maxlength="500"><?php echo html($achievement['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">
                                <?php echo bloggy_icon('bs', 'gear', '18', '#000', 'me-2'); ?>
                                <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITIONS_LABEL; ?>
                            </label>
                            
                            <div id="conditionsContainer">
                                <?php 
                                $conditionIndex = 0;
                                if (!empty($achievement['conditions'])) { 
                                    foreach ($achievement['conditions'] as $index => $condition) { ?>
                                    <div class="condition-item card mb-3">
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-md-4">
                                                    <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_TYPE_LABEL; ?></label>
                                                    <select class="form-select condition-type" name="conditions[<?php echo $index; ?>][type]">
                                                        <option value=""><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_TYPE_SELECT; ?></option>
                                                        <option value="registration_days" <?php echo $condition['condition_type'] == 'registration_days' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_REGISTRATION_DAYS; ?></option>
                                                        <option value="comments_count" <?php echo $condition['condition_type'] == 'comments_count' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_COMMENTS_COUNT; ?></option>
                                                        <option value="posts_count" <?php echo $condition['condition_type'] == 'posts_count' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_POSTS_COUNT; ?></option>
                                                        <option value="login_days" <?php echo $condition['condition_type'] == 'login_days' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_LOGIN_DAYS; ?></option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LABEL; ?></label>
                                                    <select class="form-select condition-operator" name="conditions[<?php echo $index; ?>][operator]">
                                                        <option value=">" <?php echo $condition['operator'] == '>' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_GREATER; ?></option>
                                                        <option value="<" <?php echo $condition['operator'] == '<' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LESS; ?></option>
                                                        <option value="=" <?php echo $condition['operator'] == '=' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_EQUAL; ?></option>
                                                        <option value=">=" <?php echo $condition['operator'] == '>=' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_GREATER_EQUAL; ?></option>
                                                        <option value="<=" <?php echo $condition['operator'] == '<=' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LESS_EQUAL; ?></option>
                                                        <option value="!=" <?php echo $condition['operator'] == '!=' ? 'selected' : ''; ?>><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_NOT_EQUAL; ?></option>
                                                    </select>
                                                </div>
                                                <div class="col-md-4">
                                                    <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_VALUE_LABEL; ?></label>
                                                    <input type="number" class="form-control condition-value" 
                                                        name="conditions[<?php echo $index; ?>][value]" min="0" 
                                                        value="<?php echo html($condition['value']); ?>">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-sm btn-outline-danger remove-condition" 
                                                        style="margin-bottom: 8px;">
                                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="condition-description mt-2 small text-muted">
                                                <?php 
                                                $descriptions = array(
                                                    'registration_days' => LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_REGISTRATION_DAYS,
                                                    'comments_count' => LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_COMMENTS_COUNT,
                                                    'posts_count' => LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_POSTS_COUNT,
                                                    'login_days' => LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_LOGIN_DAYS
                                                );
                                                echo $descriptions[$condition['condition_type']] ?? '';
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php 
                                    $conditionIndex = $index + 1;
                                    } 
                                } else { ?>
                                <div class="condition-item card mb-3">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_TYPE_LABEL; ?></label>
                                                <select class="form-select condition-type" name="conditions[0][type]">
                                                    <option value=""><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_TYPE_SELECT; ?></option>
                                                    <option value="registration_days"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_REGISTRATION_DAYS; ?></option>
                                                    <option value="comments_count"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_COMMENTS_COUNT; ?></option>
                                                    <option value="likes_count"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_LIKES_COUNT; ?></option>
                                                    <option value="bookmarks_count"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_BOOKMARKS_COUNT; ?></option>
                                                    <option value="login_days"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_LOGIN_DAYS; ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LABEL; ?></label>
                                                <select class="form-select condition-operator" name="conditions[0][operator]">
                                                    <option value=">"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_GREATER; ?></option>
                                                    <option value="<"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LESS; ?></option>
                                                    <option value="="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_EQUAL; ?></option>
                                                    <option value=">="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_GREATER_EQUAL; ?></option>
                                                    <option value="<="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LESS_EQUAL; ?></option>
                                                    <option value="!="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_NOT_EQUAL; ?></option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_VALUE_LABEL; ?></label>
                                                <input type="number" class="form-control condition-value" 
                                                    name="conditions[0][value]" min="0" value="1">
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-condition" 
                                                    style="margin-bottom: 8px;">
                                                    <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="condition-description mt-2 small text-muted"></div>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addCondition">
                                <?php echo bloggy_icon('bs', 'plus', '16', '#0d6efd', 'me-1'); ?>
                                <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_ADD_CONDITION_BTN; ?>
                            </button>
                            
                            <div class="form-text mt-2">
                                <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITIONS_HINT; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <?php if (!empty($achievement['image'])) { ?>
                        <div class="mb-3 text-center">
                            <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo $achievement['image']; ?>" 
                                class="img-thumbnail mb-2" 
                                style="max-width: 128px; max-height: 128px;"
                                alt="<?php echo html($achievement['name']); ?>">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="removeImage">
                                <label class="form-check-label small" for="removeImage">
                                    <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_REMOVE_IMAGE_LABEL; ?>
                                </label>
                            </div>
                        </div>
                        <?php } ?>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo !empty($achievement['image']) ? LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_REPLACE_IMAGE_LABEL : LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_UPLOAD_IMAGE_LABEL; ?>
                            </label>
                            <input type="file" class="form-control" name="image" accept="image/*" 
                                id="imageUpload">
                            <div class="form-text"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_IMAGE_HINT; ?></div>
                        </div>
                        
                        <div id="imagePreview" class="text-center mt-3" style="display: none;">
                            <img src="" alt="Preview" class="img-thumbnail" style="max-width: 128px;">
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_TYPE_LABEL; ?></label>
                            <select class="form-select" name="type">
                                <option value="auto" <?php echo ($achievement['type'] ?? 'auto') == 'auto' ? 'selected' : ''; ?>>
                                    <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_TYPE_AUTO; ?>
                                </option>
                                <option value="manual" <?php echo ($achievement['type'] ?? 'auto') == 'manual' ? 'selected' : ''; ?>>
                                    <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_TYPE_MANUAL; ?>
                                </option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_PRIORITY_LABEL; ?></label>
                            <input type="number" class="form-control" name="priority"
                                value="<?php echo html($achievement['priority'] ?? 0); ?>" min="0">
                            <div class="form-text"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_PRIORITY_HINT; ?></div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                id="isActive" <?php echo ($achievement['is_active'] ?? 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="isActive">
                                <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_ACTIVE_LABEL; ?>
                            </label>
                        </div>
                        
                        <div class="text-muted small">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CREATED_AT; ?></span>
                                <span><?php echo date('d.m.Y H:i', strtotime($achievement['created_at'])); ?></span>
                            </div>
                            <?php if ($achievement['updated_at'] != $achievement['created_at']) { ?>
                            <div class="d-flex justify-content-between">
                                <span><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_UPDATED_AT; ?></span>
                                <span><?php echo date('d.m.Y H:i', strtotime($achievement['updated_at'])); ?></span>
                            </div>
                            <?php } ?>
                            <?php if (isset($achievement['user_count'])) { ?>
                            <div class="d-flex justify-content-between">
                                <span><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_USER_COUNT; ?></span>
                                <span><?php echo $achievement['user_count']; ?> <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_USERS; ?></span>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?>
                        <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_SUBMIT_BTN; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/user-achievements" class="btn btn-outline-secondary">
                        <?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CANCEL_BTN; ?>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let conditionIndex = <?php echo $conditionIndex; ?>;
    const conditionTemplates = {
        'registration_days': '<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_REGISTRATION_DAYS; ?>',
        'comments_count': '<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_COMMENTS_COUNT; ?>',
        'posts_count': '<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_POSTS_COUNT; ?>',
        'login_days': '<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_DESC_LOGIN_DAYS; ?>'
    };
    
    document.getElementById('addCondition').addEventListener('click', function() {
        const container = document.getElementById('conditionsContainer');
        const newCondition = document.createElement('div');
        newCondition.className = 'condition-item card mb-3';
        newCondition.innerHTML = `
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_TYPE_LABEL; ?></label>
                        <select class="form-select condition-type" name="conditions[${conditionIndex}][type]">
                            <option value=""><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_TYPE_SELECT; ?></option>
                            <option value="registration_days"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_REGISTRATION_DAYS; ?></option>
                            <option value="comments_count"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_COMMENTS_COUNT; ?></option>
                            <option value="posts_count"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_POSTS_COUNT; ?></option>
                            <option value="login_days"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_LOGIN_DAYS; ?></option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LABEL; ?></label>
                        <select class="form-select condition-operator" name="conditions[${conditionIndex}][operator]">
                            <option value=">"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_GREATER; ?></option>
                            <option value="<"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LESS; ?></option>
                            <option value="="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_EQUAL; ?></option>
                            <option value=">="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_GREATER_EQUAL; ?></option>
                            <option value="<="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_LESS_EQUAL; ?></option>
                            <option value="!="><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_OPERATOR_NOT_EQUAL; ?></option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small"><?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_CONDITION_VALUE_LABEL; ?></label>
                        <input type="number" class="form-control condition-value" 
                            name="conditions[${conditionIndex}][value]" min="0" value="1">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-condition" 
                            style="margin-bottom: 8px;">
                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                        </button>
                    </div>
                </div>
                <div class="condition-description mt-2 small text-muted"></div>
            </div>
        `;
        container.appendChild(newCondition);
        conditionIndex++;
        addConditionHandlers(newCondition);
    });
    
    function addConditionHandlers(conditionElement) {
        const typeSelect = conditionElement.querySelector('.condition-type');
        const removeBtn = conditionElement.querySelector('.remove-condition');
        const descriptionDiv = conditionElement.querySelector('.condition-description');
        
        typeSelect.addEventListener('change', function() {
            const desc = conditionTemplates[this.value] || '';
            descriptionDiv.textContent = desc;
        });
        
        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.condition-item').length > 1) {
                conditionElement.remove();
            } else {
                alert('<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_ONE_CONDITION_ALERT; ?>');
            }
        });
        
        if (typeSelect.value) {
            descriptionDiv.textContent = conditionTemplates[typeSelect.value] || '';
        }
    }
    
    document.querySelectorAll('.condition-item').forEach(addConditionHandlers);
    
    document.getElementById('imageUpload')?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                const img = preview.querySelector('img');
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    const iconInput = document.querySelector('input[name="icon"]');
    const iconPreview = document.getElementById('iconPreviewIcon');
    
    iconInput?.addEventListener('input', function() {
        const iconName = this.value.trim();
        if (iconName) {
            iconPreview.className = 'bi bi-' + iconName;
        } else {
            iconPreview.className = 'bi bi-trophy';
        }
    });

    document.getElementById('achievementForm').addEventListener('submit', function(e) {
        const nameInput = this.querySelector('input[name="name"]');
        if (!nameInput.value.trim()) {
            e.preventDefault();
            alert('<?php echo LANG_TEMPLATE_USERS_ACHIEVEMENT_EDIT_ALERT_NAME_REQUIRED; ?>');
            nameInput.focus();
            return;
        }
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>