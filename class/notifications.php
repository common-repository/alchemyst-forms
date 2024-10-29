<?php
/**
 * Static notifications helper class
 */

class Alchemyst_Forms_Notifications {

    /**
     * Get Alchemst_Forms_Notification objects for a provided form
     *
     * @param $form_id - Form ID (Parent) to retrieve notifications for.
     */
    public static function get_notifications($form_id) {
        $args = array(
            'post_type' => AF_NOTIFICATIONS_POSTTYPE,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_alchemyst_forms-form-id',
                    'value' => $form_id
                )
            ),
            'orderby' => 'ID',
            'order' => 'asc'
        );

        $posts = get_posts($args);
        $notifications = array();

        foreach($posts as $post) {
            $n = new Alchemst_Forms_Notification($post->ID);
            $n = apply_filters('alchemyst_forms:build-notification-object:' . $n->type, $n);
            $notifications[] = $n;
        }

        return $notifications;
    }

    /**
     * Save notifications
     *
     * @param $form_id - ID of the form (parent) of this notification
     * @param $notifications - $_POST['alchemyst-forms-notification'] from edit.php
     */
    public static function save($form_id, $notifications) {
        $args = array(
            'post_type' => AF_NOTIFICATIONS_POSTTYPE,
            'meta_query' => array(
                array(
                    'key' => '_alchemyst_forms-form-id',
                    'value' => $form_id,
                    'compare' => '=',
                    'type' => 'NUMERIC'
                )
            )
        );
        $existing_notifications = get_posts($args);

        foreach ($existing_notifications as $en) {
            if (!isset($notifications[$en->ID])) {
                wp_trash_post($en->ID);
            }
        }


        foreach ($notifications as $key => $notification) {
            if (strpos($key, 'new') !== false) {
                self::route_notification_save($notification, $form_id);
            }
            else {
                // Existing
                self::route_notification_update($notification, $form_id);
            }
        }
    }

    /**
     * Route things around, this is mostly in preparation for when multiple notification types will be available.
     *
     * @param $notification - Alchemst_Forms_Notification object
     * @param $form_id - ID of the form that generated this notification.
     */
    public static function route_notification_save($notification, $form_id) {
        switch ($notification['type']) {
            case 'email':
                self::save_new_email_notification($notification, $form_id);
                break;
            default:
                do_action('alchemyst_forms:save-notification-type:' . $notification['type'], $notification, $form_id);
                break;
        }
    }

    /**
     * Routes updates to the appropriate method.
     *
     * @param $notification - Alchemst_Forms_Notification object
     * @param $form_id - ID of the form that generated this notification.
     */
    public static function route_notification_update($notification, $form_id) {
        switch ($notification['type']) {
            case 'email':
                self::update_email_notification($notification, $form_id);
                break;
            default:
                do_action('alchemyst_forms:update-notification-type:' . $notification['type'], $notification, $form_id);
                break;
        }
    }

    /**
     * Save new email notification
     *
     * @param $notification - Alchemst_Forms_Notification object
     * @param $form_id - ID of the form that generated this notification.
     */
    public static function save_new_email_notification($notification, $form_id) {
        $post_arr = self::build_email_post_arr($notification, $form_id);
        $post_id = wp_insert_post($post_arr);
        return $post_id;
    }

    /**
     * Update existing email notification
     *
     * @param $notification - Alchemst_Forms_Notification object
     * @param $form_id - ID of the form that generated this notification.
     */
    public static function update_email_notification($notification, $form_id) {
        $post_arr = self::build_email_post_arr($notification, $form_id);
        $post_arr['ID'] = $notification['id'];
        $post_id = wp_update_post($post_arr);
        return $post_id;
    }

    /**
     * Builds the arguments for wp_insert_post or wp_update_post
     *
     * @param $notification - Alchemst_Forms_Notification object
     * @param $form_id - ID of the form that generated this notification.
     */
    public static function build_email_post_arr($notification, $form_id) {
        return array(
            'post_type' => AF_NOTIFICATIONS_POSTTYPE,
            'post_status' => 'publish',
            'post_title' => 'Email Notification for Form: ' . $form_id,
            'meta_input' => array(
                '_alchemyst_forms-form-id'                => $form_id,
                '_alchemyst_forms-notification-type'      => $notification['type'],
                '_alchemyst-forms-notification-to'        => $notification['to'],
                '_alchemyst-forms-notification-cc'        => $notification['cc'],
                '_alchemyst-forms-notification-bcc'       => $notification['bcc'],
                '_alchemyst-forms-notification-from'      => $notification['from'],
                '_alchemyst-forms-notification-from_name' => $notification['from_name'],
                '_alchemyst-forms-notification-subject'   => $notification['subject'],
                '_alchemyst-forms-notification-headers'   => $notification['headers'],
                '_alchemyst-forms-notification-email'     => stripslashes($notification['email']),
                '_alchemyst-forms-notification-files'     => $notification['files'],
                '_alchemyst-forms-notification-template'  => $notification['template']
            )
        );
    }
}

/**
 * Hopefully this naming convention isn't strange or confusing
 * Really ultra basic class for the individual notifications.
 */
class Alchemst_Forms_Notification {

    function __construct($notification_id) {
        $this->ID = $notification_id;
        $this->post = get_post($notification_id);
        $this->form_id = get_post_meta($notification_id, '_alchemyst_forms-form-id', true);
        $this->type = get_post_meta($notification_id, '_alchemyst_forms-notification-type', true);

        // TODO: abstract this away from tons of get_post_meta calls if possible, or at least make easier to extend
        if ($this->type == 'email') {
            $this->to        = get_post_meta($notification_id, '_alchemyst-forms-notification-to', true);
            $this->cc        = get_post_meta($notification_id, '_alchemyst-forms-notification-cc', true);
            $this->bcc       = get_post_meta($notification_id, '_alchemyst-forms-notification-bcc', true);
            $this->from_name = get_post_meta($notification_id, '_alchemyst-forms-notification-from_name', true);
            $this->from      = get_post_meta($notification_id, '_alchemyst-forms-notification-from', true);
            $this->subject   = get_post_meta($notification_id, '_alchemyst-forms-notification-subject', true);
            $this->headers   = get_post_meta($notification_id, '_alchemyst-forms-notification-headers', true);
            $this->email     = get_post_meta($notification_id, '_alchemyst-forms-notification-email', true);
            $this->files     = get_post_meta($notification_id, '_alchemyst-forms-notification-files', true);
            $this->template  = get_post_meta($notification_id, '_alchemyst-forms-notification-template', true);
        }
    }
}
