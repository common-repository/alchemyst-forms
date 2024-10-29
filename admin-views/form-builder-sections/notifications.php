<?php
    global $post;
    $notifications = array();
    $screen = get_current_screen();

    if ($screen->action != 'add') {
        $notifications = Alchemyst_Forms_Notifications::get_notifications($post->ID);
    }

    $default_notification_types = array(
        'email' => array(
            'name' => 'Email',
            'dashicon' => 'email-alt',
            'template-copy' => 'notifications/email-copy-template.php',
            'template-main' => 'notifications/email-template.php',
        )
    );
    $notification_types = apply_filters('alchemyst_forms:notification-types', $default_notification_types);

    $email_templates = Alchemyst_Forms_Email::get_templates();
?>
<p id="alchemyst-forms-notification-controls">
    <p>
        <h3>Available Field Shortcodes <small>(Click to copy)</small></h3>
        <div class="alchemyst-forms-field-names"></div><?php /* This container is populated via js (see js/admin.js) */ ?>
    </p>
    <p>
        <h3>Add New Notification</h3>
        <label for="alchemyst-forms-notification-type">Notification Type:</label>
        <select id="alchemyst-forms-notification-type" name="alchemyst-forms-notification-type">
            <?php foreach ($notification_types as $type => $args) :?>
                <option value="<?=$type?>"><?=$args['name']?></option>
            <?php endforeach; ?>
        </select>
        <a id="alchemyst-forms-add-notification" class="button button-primary button-large" href="#notifications"><span class="dashicons dashicons-plus"></span> Add Notification</a>
    </p>
</p>

<?php foreach ($notification_types as $type => $args) : ?>
    <div class="alchemyst-forms-notification-template" data-alchemyst-forms-template="<?=$type?>" style="display: none !important;">
        <div class="alchemyst-forms-notification alchemyst-forms-notification-<?=$type?>" data-notification-id="{id}">
            <input type="hidden" name="alchemyst-forms-notification[{id}][type]" value="<?=$type?>">
            <div class="collapse-header">
                <h3>
                    <span class="dashicons dashicons-<?=$args['dashicon']?> icon-left"></span>
                    <?=$args['name']?> (*new*)
                    <span class="dashicons dashicons-arrow-up icon-right"></span>
                </h3>
            </div>
            <div class="collapsable open">
                <?php include $args['template-copy']; ?>
                <div class="alchemyst-forms-delete-notification-container alchemyst-forms-text-right">
                    <a href="#notifications" class="delete-notification">Delete Notification</a>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<div id="alchemyst-forms-notifications-wrap">
    <?php foreach($notifications as $notification) : ?>
        <?php if (!isset($notification_types[$notification->type])) continue; ?>
        <div class="alchemyst-forms-notification alchemyst-forms-notification-<?=$type?>" data-notification-id="<?=$notification->ID?>">
            <input type="hidden" name="alchemyst-forms-notification[<?=$notification->ID?>][type]" value="<?=$notification->type?>">
            <input type="hidden" name="alchemyst-forms-notification[<?=$notification->ID?>][id]" value="<?=$notification->ID?>">
            <div class="collapse-header">
                <h3>
                    <span class="dashicons dashicons-<?=$notification_types[$notification->type]['dashicon']?> icon-left"></span>
                    <?=$notification_types[$notification->type]['name']?> Notification (ID: <?=$notification->ID?>)
                    <span class="dashicons dashicons-arrow-up icon-right"></span>
                </h3>
            </div>
            <div class="collapsable">
                <?php include $notification_types[$notification->type]['template-main']; ?>
                <div class="alchemyst-forms-delete-notification-container alchemyst-forms-text-right">
                    <a href="#notifications" class="delete-notification">Delete Notification</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
