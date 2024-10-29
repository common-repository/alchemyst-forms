<?php
/**
 * Ajaxy stuff.
 */
use PHPHtmlParser\Dom;

class Alchemyst_Forms_Ajax {

    /**
     * Initialize the Wordpress actions.
     *
     * admin-ajax methods:
     *      alchemyst-form-submit - Handles the frontend form submission for contact forms.
     *      alchemyst-forms-save-entry-view - Saves the entry view when modifying the entries view table from the admin side.
     *          Does not support nopriv
     */
    public static function init() {
        // Frontend form submission. Nonced.
        add_action('wp_ajax_alchemyst-form-submit', array(__CLASS__, 'form_submit'));
        add_action('wp_ajax_nopriv_alchemyst-form-submit', array(__CLASS__, 'form_submit'));

        // Backend entry view saver. Not nonced. Nopriv does nothing at the moment.
        add_action('wp_ajax_alchemyst-forms-save-entry-view', array(__CLASS__, 'save_entry_view'));
        add_action('wp_ajax_nopriv_alchemyst-forms-save-entry-view', array(__CLASS__, 'save_entry_view_nopriv'));

        add_action('wp_ajax_alchemyst-forms-delete-entry', array(__CLASS__, 'delete_entry'));
        add_action('wp_ajax_nopriv_alchemyst-forms-delete-entry', array(__CLASS__, 'delete_entry_nopriv'));

        add_action('wp_ajax_alchemyst-forms-delete-entry-undo', array(__CLASS__, 'delete_entry_undo'));
        add_action('wp_ajax_nopriv_alchemyst-forms-delete-entry-undo', array(__CLASS__, 'delete_entry_undo_nopriv'));
    }

    // Front end form submission
    public static function form_submit() {
        $nonce_check = Alchemyst_Forms_Utils::verify_nonce($_REQUEST['_wpnonce'], '_alchemyst_forms_nonce');
        if ($nonce_check === false || $nonce_check == 0 || $nonce_check == -1) {
            self::json_response('Something went wrong. Your nonce could not be verified.');
        }

        $form_id = (int) $_REQUEST['form_id'];

        if (!$form_id) {
            self::json_response('Something went wrong. No Form ID specified.');
        }

        $response = Alchemyst_Forms_Submissions::handle_submission($form_id, $_REQUEST);

        self::json_response($response);
    }

    /**
     * Saves the entry view. Very simple meta update.
     *
     * This entry view is based on the current logged in user_id, so that view settings are maintained depending on
     * which user account is being used to view the entries.
     */
    public static function save_entry_view() {
        $form_id = $_REQUEST['form_id'];
        $visible_fields = $_REQUEST['visible-fields'];
        $field_order = $_REQUEST['field-order'];
        $user_id = get_current_user_id();
        Alchemyst_Form::save_entry_view_settings($form_id, $user_id, $visible_fields, $field_order);

        self::json_response(true);
    }

    // ?? Not sure about this. I think this can literally do nothing.
    public static function save_entry_view_nopriv() {
        self::json_response(array());
    }

    // Ajax call to delete an entry - should create a more graceful experience in the delete entry process.
    public static function delete_entry() {
        $entry_id = $_REQUEST['entry_id'];

        if (!current_user_can('delete_af-entry')) {
            self::json_response(array('error' => 'Permission denied.'));
        }

        $r = wp_trash_post($entry_id);

        if ($r === false) {
            self::json_response(array('error' => 'Post already in trash.'));
        }

        self::json_response(array('success' => true));
    }

    // ?? Not sure about this. I think this can literally do nothing.
    public static function delete_entry_nopriv() {
        self::json_response(array());
    }

    // Ajax call to delete an entry - should create a more graceful experience in the delete entry process.
    public static function delete_entry_undo() {
        $entry_id = $_REQUEST['entry_id'];

        if (!current_user_can('delete_af-entry')) {
            self::json_response(array('error' => 'Permission denied.'));
        }

        $r = wp_untrash_post($entry_id);

        if ($r === false) {
            self::json_response(array('error' => 'Post not in trash.'));
        }

        self::json_response(array('success' => 'test'));
    }

    // ?? Not sure about this. I think this can literally do nothing.
    public static function delete_entry_undo_nopriv() {
        self::json_response(array());
    }

    /**
     * Prepares and executes a json response. Sets the header content type, json encodes some output object, then exits.
     *
     * @param $output_obj - Object to json_encode and return as a json response.
     */
    public static function json_response($output_obj) {
        header('Content-Type: application/json');
        print(json_encode($output_obj));
        die();
    }
}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Ajax', 'init'));
