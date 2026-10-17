<?php
/**
* Template Name: Запись фрагмента
*/
?>
<div class="tg-page">
    <div class="tg-container">
        <div class="tg-page-card tg-mt-5">
            <div class="tg-page-header tg-mb-4">
                <div class="tg-page-meta">
                    <span class="tg-meta-item">
                        <?php echo bloggy_icon('bs', 'puzzle', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo html($fragment['name']); ?>
                    </span>
                </div>
                <h1 class="tg-page-title"><?php echo html($entryTitle); ?></h1>
            </div>
            <div class="tg-page-content fragment-entry-content">
                <?php echo $entryContent; ?>
            </div>
        </div>
    </div>
</div>
