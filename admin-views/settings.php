<?php
    $default_settings_sections = array(
        'pro-license' => array(
            'name' => 'Pro Upgrade',
            'template' => 'settings-sections/pro.php',
        ),
        'bootstrap' => array(
            'name' => 'Bootstrap Styles',
            'template' => 'settings-sections/bootstrap.php'
        ),
        'recaptcha-settings' => array(
            'name' => 'reCAPTCHA',
            'template' => 'settings-sections/recaptcha-settings.php',
        ),
        'email-settings' => array(
            'name' => 'SMTP Emails',
            'template' => 'settings-sections/email-settings.php',
        ),
        'nonstandard-input-settings' => array(
            'name' => 'Non-Standard Input Settings',
            'template' => 'settings-sections/nonstandard-inputs.php',
        ),
        'editor-settings' => array(
            'name' => 'Editor Settings',
            'template' => 'settings-sections/editor-settings.php',
        ),
        'file-upload-settings' => array(
            'name' => 'File Uploads',
            'template' => 'settings-sections/file-upload-settings.php',
        ),
        'default-messages' => array(
            'name' => 'Messages',
            'template' => 'settings-sections/default-messages.php'
        )
    );

    $settings_sections = apply_filters('alchemyst_forms:settings-sections', $default_settings_sections);
    $license = Alchemyst_Forms_License::get_instance();
?>

<?php if ($_POST) : ?>
    <div class="notice notice-success">
        <p>
            <strong>Success:</strong> Settings updated!
        </p>
    </div>
<?php endif; ?>
<?php if (!$license->license_is_valid() &&
        !empty($license->license_key) &&
        !isset($_POST['pro-license-key']) &&
        !$license->license_is_free_version()
    ) : ?>
    <div class="notice notice-error">
        <p>
            <strong>Error:</strong> We couldn't verify that your license key is active and valid.
        </p>
    </div>
<?php elseif (isset($_POST['pro-license-key'])) :
    delete_transient('_alchemyst_forms-license-check');
    $license->license_key = $_POST['pro-license-key'];
    $license->check_license();

    if (!$license->license_is_valid()) : ?>

        <?php if (isset($_POST['pro-license-key']) && !empty($_POST['pro-license-key'])) : ?>
            <div class="notice notice-error">
                <p>
                    <strong>Error:</strong> We couldn't verify that your license key is active and valid.
                </p>
            </div>
        <?php endif;
    endif;
endif; ?>
<div class="wrap">
    <?=Alchemyst_Forms_Utils::render_logo()?>

    <h1>Alchemyst Forms Settings</h1>

    <div id="alchemyst-forms-tabs">
        <?php $i = 0; foreach ($settings_sections as $section_tag => $section) : ?>
            <a class="nav-tab<?=($i == 0 ? ' nav-tab-active' : null)?>" href="#<?=$section_tag?>"><?=$section['name']?></a>
        <?php $i++; endforeach; ?>
        <div style="clear: both;"></div>
    </div>


    <form method="post">

        <?php $i = 0; foreach ($settings_sections as $section_tag => $section) : ?>
            <div class="alchemyst-forms-tab-section<?=($i == 0 ? ' alchemyst-forms-tab-section-active' : null)?>" data-alchemyst-forms-tab-section="#<?=$section_tag?>">
                <?php include $section['template']; ?>
            </div>
        <?php $i++; endforeach; ?>

        <input type="submit" class="button button-primary button-large" value="Update ALL Settings">
    </form>
</div>
