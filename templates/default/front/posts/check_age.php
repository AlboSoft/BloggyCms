<div class="tg-age-check-page">
    <div class="tg-container">
        <div class="tg-age-check-card">
            <div class="tg-age-check-icon">
                <?php echo bloggy_icon('bs', '18-plus-circle', '80', '#dc3545'); ?>
            </div>
            
            <h1 class="tg-age-check-title"><?php echo LANG_TEMPLATE_AGE_CHECK_TITLE; ?></h1>
            
            <p class="tg-age-check-text">
                <?php echo sprintf(LANG_TEMPLATE_AGE_CHECK_TEXT, $min_age); ?>
            </p>
            
            <?php if (isset($error)) { ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php } ?>
            
            <form method="POST" class="tg-age-check-form">
                <div class="tg-age-check-fields">
                    <div class="tg-age-field">
                        <label><?php echo LANG_TEMPLATE_AGE_CHECK_DAY; ?></label>
                        <select name="day" class="form-select" required>
                            <option value="">--</option>
                            <?php for ($i = 1; $i <= 31; $i++) { ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="tg-age-field">
                        <label><?php echo LANG_TEMPLATE_AGE_CHECK_MONTH; ?></label>
                        <select name="month" class="form-select" required>
                            <option value="">--</option>
                            <?php for ($i = 1; $i <= 12; $i++) { ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="tg-age-field">
                        <label><?php echo LANG_TEMPLATE_AGE_CHECK_YEAR; ?></label>
                        <select name="year" class="form-select" required>
                            <option value="">----</option>
                            <?php for ($i = date('Y'); $i >= date('Y') - 100; $i--) { ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg">
                    <?php echo LANG_TEMPLATE_AGE_CHECK_BTN; ?>
                </button>
            </form>
            
            <p class="tg-age-check-footer">
                <a href="<?php echo BASE_URL; ?>"><?php echo LANG_TEMPLATE_AGE_CHECK_BACK; ?></a>
            </p>
        </div>
    </div>
</div>