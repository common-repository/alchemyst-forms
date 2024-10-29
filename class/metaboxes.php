<?php
/**
 * Register metaboxes
 */
class Alchemyst_Forms_Metaboxes {

    /**
     * Registers the wordpress hooks/actions that this class uses.
     */
    public static function init() {
        add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'));
        add_action('save_post', array(__CLASS__, 'save_meta'), 10, 3);
        add_action('admin_notices', array(__CLASS__, 'edit_notices'));
    }

    /**
     * Add meta boxes
     */
    public static function add_meta_boxes() {
        // Add the form builder, primary metabox for this plugin's primary post type.
        add_meta_box('af-form-builder', 'Form Settings', array(__CLASS__, 'form_builder_callback'), AF_POSTTYPE, 'normal', 'high');

        // Shortcode metabox should only be on AF_POSTTYPE pages if we're not adding a new one.
        $screen = get_current_screen();
        if ($screen->action != "add") {
            add_meta_box('af-form-shortcode', 'Shortcode', array(__CLASS__, 'form_shortcode_callback'), AF_POSTTYPE, 'side', 'high');
        }

        // Add a metabox for entry data for the entries post type.
        add_meta_box('af-entry-data', 'Entry Details', array(__CLASS__, 'entry_details_callback'), AF_ENTRIES_POSTTYPE, 'normal', 'high');
    }

    /**
     * Callback to load the admin template for the form builder
     */
    public static function form_builder_callback() {
        echo Alchemyst_Forms_Utils::load_admin_template('form-builder');
    }

    /**
     * Callback to load the admin template for the shortcode
     */
    public static function form_shortcode_callback() {
        echo Alchemyst_Forms_Utils::load_admin_template('form-shortcode');
    }

    /**
     * Save post meta
     * TODO: Add filters or actions?
     * TODO: Make more extensible?
     */
    public static function save_meta($post_id, $post, $update) {
        unset($_POST['alchemyst-forms-notification']['{id}']);

        if ($post->post_type != AF_POSTTYPE)
            return;

        if (isset($_POST['contact-form-code'])) {
            $code = stripslashes($_POST['contact-form-code']);
            $code = apply_filters('alchemyst_forms:pre-save-contact-form', $code, $post_id, $post, $update);
            update_post_meta($post_id, '_alchemyst_forms_contact-form-code', $code);
        }

        if (isset($_POST['alchemyst-forms-notification'])) {
            Alchemyst_Forms_Notifications::save($post_id, $_POST['alchemyst-forms-notification']);
        }

        self::save_post_meta($post_id, $_POST);
    }

    public static function save_post_meta($post_id, $postdata) {
        // key => default value
        $meta_keys = array(
            '_alchemyst_forms-submission-action' => 'show-success-message',
            '_af-success-message' => 'Your message has been successfully received. Thank you.',
            '_alchemyst_forms-redirect-url' => home_url(),
            '_alchemyst_forms-ga-submission-tracking' => '1',
            '_alchemyst_forms-ga-submission-tracking-event-name' => "Contact Form ID {$post_id} Submitted"
        );

        $meta_keys = apply_filters('alchemyst_forms:postmeta-keys', $meta_keys);

        foreach ($meta_keys as $meta_key => $default_value) {
            if (isset($postdata[$meta_key])) {
                update_post_meta($post_id, $meta_key, $postdata[$meta_key]);
            }
            else {
                update_post_meta($post_id, $meta_key, $default_value);
            }
        }
    }

    /**
     * Form builder notices.
     */
    public static function edit_notices() {
        $screen = get_current_screen();
        if ($screen->parent_file == 'edit.php?post_type=alchemyst-forms' && $screen->action != "add" && $screen->base == "post")
            echo Alchemyst_Forms_Utils::load_admin_template('form-builder-notices');
    }

    /**
     * Callback to load the admin template for the entry details metabox.
     */
    public static function entry_details_callback() {
        global $post;
        global $entry;
        $entry = new Alchemst_Forms_Entry(null, $post); // TODO: This looks weird.

        echo Alchemyst_Forms_Utils::load_admin_template('entry-details');
    }

}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Metaboxes', 'init'));
