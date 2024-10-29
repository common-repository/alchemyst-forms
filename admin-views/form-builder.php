<?php
    global $post;
    $form_id = $post->ID;
    $screen = get_current_screen();

    $default_sections = array(
        'form-builder' => array(
            'name' => 'Form Builder',
            'template' => 'form-builder-sections/form-builder.php',
            'show_on_new_form' => true,
            'capability' => 'af-edit-forms',
        ),
        'notifications' => array(
            'name' => 'Notifications',
            'template' => 'form-builder-sections/notifications.php',
            'show_on_new_form' => true,
            'capability' => 'af-edit-notifications',
        ),
        'entries' => array(
            'name' => 'Entries',
            'template' => 'form-builder-sections/entries.php',
            'show_on_new_form' => false,
            'capability' => 'af-view-entries',
        ),
        'additional-settings' => array(
            'name' => 'Additional Settings',
            'template' => 'form-builder-sections/additional-settings.php',
            'show_on_new_form' => true,
            'capability' => 'af-edit-form-additional-settings',
        ),
    );
    $sections = apply_filters('alchemyst_forms:form-builder-sections', $default_sections);

?>
<?php Alchemyst_Forms_Utils::render_logo() ?>
<div id="alchemyst-forms-tabs">
    <?php $i = 0; foreach ($sections as $section_tag => $section) : ?>
        <?php
            // Some sections have no purpose for new contact forms
            if ($screen->action == 'add' && !$section['show_on_new_form']) continue;
            // Do not display if current user doesn't have capabilities
            if (!current_user_can($section['capability'])) continue;
            // Allow filter to prevent users from viewing if desirable.
            if (!apply_filters('alchemyst_forms:form-builder-view-section:' . $section_tag, true, $form_id)) continue;
        ?>
        <a class="nav-tab<?=($i == 0 ? ' nav-tab-active' : null)?>" href="#<?=$section_tag?>"><?=$section['name']?></a>
    <?php $i++; endforeach; ?>
    <div style="clear: both;"></div>
</div>

<?php $i = 0; foreach ($sections as $section_tag => $section) : ?>
    <?php
        // Some sections have no purpose for new contact forms
        if ($screen->action == 'add' && !$section['show_on_new_form']) continue;
        // Do not display if current user doesn't have capabilities
        if (!current_user_can($section['capability'])) continue;
        // Allow filter to prevent users from viewing if desirable.
        if (!apply_filters('alchemyst_forms:form-builder-view-section:' . $section_tag, true, $form_id)) continue;
    ?>
    <div class="alchemyst-forms-tab-section<?=($i == 0 ? ' alchemyst-forms-tab-section-active' : null)?><?=($section_tag == 'entries' ? ' af-tab-section-show-until-init' : null)?>" data-alchemyst-forms-tab-section="#<?=$section_tag?>">
        <?php include $section['template']; ?>
    </div>
<?php $i++; endforeach; ?>
