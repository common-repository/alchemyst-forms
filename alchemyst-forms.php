<?php
/*
Plugin Name: Alchemyst Forms
Plugin URI: https://alchemyst.io
Description: Contact forms done right.
Version: 1.1.8
Author: Alchemyst
Author URI: https://alchemyst.io

---

Alchemyst Forms
Copyright (C) 2016 Alchemyst

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


if ( !function_exists( 'add_action' ) ) {
	exit;
}

// __FILE__ just does not work for some ridiculous reason...
register_activation_hook(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/alchemyst-forms.php', array( 'Alchemyst_Forms', 'install' ) );
register_deactivation_hook(WP_PLUGIN_DIR . '/' . basename(dirname(__FILE__)) . '/alchemyst-forms.php', array( 'Alchemyst_Forms', 'uninstall' ) );

// Constants used by this plugin.
define('ALCHEMYST_FORMS_TEXTDOMAIN', 'alchemyst-forms');
define('ALCHEMYST_FORMS__PLUGIN_URL', plugin_dir_url(__FILE__));
define('ALCHEMYST_FORMS__PLUGIN_DIR', __DIR__);
define('AF_POSTTYPE', 'alchemyst-forms');
define('AF_ENTRIES_POSTTYPE', 'af-entries');
define('AF_NOTIFICATIONS_POSTTYPE', 'af-notifications');
define('AF_SCRIPTVERSION', '1.1.8');
define('AF_SL_STORE_URL', 'https://alchemyst.io/');
define('AF_SL_ITEM_NAME', 'Alchemyst Forms');

// Load composer libraries
require 'vendor/autoload.php';

// Load plugin classes.
require_once __DIR__ . '/class/license.php';
$af_license = Alchemyst_Forms_License::get_instance();
foreach ( glob( __DIR__ . '/class/*.php' ) as $filename ) include_once $filename;

// The huge init action! Any classes that have init() functions will likely hook here.
// This mostly just ensures that all classes have access to each other's methods.
do_action('alchemyst_forms:loaded');

/**
 * Main contact class. Mostly in charge of setup, and basic Wordpress registering.
 */
class Alchemyst_Forms {

    public static function init() {
        self::register_post_types();
        self::anonymous_hooks();
        add_action('admin_enqueue_scripts', array(__CLASS__, 'admin_scripts'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'frontend_scripts'));

        add_action('manage_' . AF_POSTTYPE . '_posts_columns', array(__CLASS__, 'custom_columns'));
        add_action('manage_' . AF_POSTTYPE . '_posts_custom_column', array(__CLASS__, 'custom_column_values'), 10, 2);

        // Could be useful if you need to add quickedit actions on the custom columns or something?
        do_action('alchemyst_forms:init');

        add_action('alchemyst_forms:after-form', array('Alchemyst_Forms_Utils', 'render_ga_tracking'));
        add_action('admin_notices', array('Alchemyst_Forms_Utils', 'render_postlist_logo'));
    }

    public static function install() {
        // Write the encryption key now.
        Alchemyst_Forms_Utils::write_encryption_key();

        // Add capabilities
        Alchemyst_Forms_Capabilities::add_caps();
    }

    public static function uninstall() {
        // Remove Capabilities
        Alchemyst_Forms_Capabilities::remove_caps();
    }

    // For very very simple hooks.
    public static function anonymous_hooks() {
        add_filter( 'template_include', function ($t) {
            $GLOBALS['current_theme_template'] = basename($t);
            return $t;
        }, 1000 );
    }

    public static function register_post_types() {
        // Main Post type - is not public, but has an admin interface.
        $args = array(
            'public' => false,
            'publicly_queryable' => false,
            'supports' => array('title'),
            'menu_icon' => 'dashicons-email',
            'capability_type' => 'af-form'
        );
        Alchemyst_Forms_Utils::register_post_type(AF_POSTTYPE, 'Contact Form', 'Contact Forms', $args);

        // Ancillary post types - no admin interface, same args otherwise.
        $args['show_in_nav_menus'] = false;
        $args['show_in_menu'] = false;
        $args['capability_type'] = array('af-entry', 'af-entries');
        Alchemyst_Forms_Utils::register_post_type(AF_ENTRIES_POSTTYPE, 'Contact Form Entry', 'Contact Form Entries', $args);

        $args['capability_type'] = 'af-notification';
        Alchemyst_Forms_Utils::register_post_type(AF_NOTIFICATIONS_POSTTYPE, 'Contact Form Notification', 'Contact Form Notifications', $args);
    }

    public static function custom_columns($columns) {
        return array(
            'cb' => $columns['cb'],
            'title' => $columns['title'],
            'entries' => __('Entries', ALCHEMYST_FORMS_TEXTDOMAIN),
            'shortcode' => __('Shortcode', ALCHEMYST_FORMS_TEXTDOMAIN),
            'date' => $columns['date']
        );
    }

    public static function custom_column_values($column, $post_id) {
        switch ($column) {
            case 'shortcode':
                ?>
                    <input type="text" readonly value='[alchemyst-form id="<?=$post_id?>"]' style="width: 100%; max-width: 230px;" class="alchemyst-forms-click-highlight">
                <?php
                break;
            case 'entries':
                ?>
                    <?=Alchemyst_Forms_Entries::get_entry_count($post_id);?>
                <?php
                break;
        }
    }

    public static function admin_scripts() {

        $license = Alchemyst_Forms_License::get_instance();

        global $post;
        $screen = get_current_screen();

        // Admin CSS
        wp_enqueue_style('alchemyst-forms-admin-css', ALCHEMYST_FORMS__PLUGIN_URL . 'css/admin.css', array(), AF_SCRIPTVERSION);

        // Ace JS
        wp_enqueue_script('acejs', ALCHEMYST_FORMS__PLUGIN_URL . 'js/ace/ace.js', array('jquery'));

        $localize_args = array(
            'ajax_url' => get_admin_url() . 'admin-ajax.php',
            'ace_theme' => Alchemyst_Forms_Settings::get_setting('editor-syntax'),
            'editor_preferred_line_length' => intval(Alchemyst_Forms_Settings::get_setting('editor-preferred-line-length')),
            'editor_font_size' => intval(Alchemyst_Forms_Settings::get_setting('editor-font-size')),

            'active_license' => $license->license_is_valid()
        );

        // Only do some stuff if we're on a relevant admin page...
        if ($screen->action != 'add' && is_object($post) && $post->post_type == AF_POSTTYPE) {
            // Datatables
            wp_enqueue_script('datatables', 'https://cdn.datatables.net/u/dt/dt-1.10.12,b-1.2.0,b-colvis-1.2.0,cr-1.3.2/datatables.min.js', array('jquery'));

            if (isset($_GET['post'])) {
                $localize_args['entry_fields'] = Alchemyst_Form::get_field_names_by_id($post->ID);
                $localize_args['form_id'] = $post->ID;
            }

            // Admin Javascript
            wp_enqueue_script('prettify-html', ALCHEMYST_FORMS__PLUGIN_URL . 'js/prettify.js');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('alchemyst-forms-admin-js', ALCHEMYST_FORMS__PLUGIN_URL . 'js/admin.js', array('jquery', 'acejs', 'datatables', 'prettify-html', 'jquery-ui-sortable'), AF_SCRIPTVERSION);
        }
        else {
            // Admin Javascript
            wp_enqueue_script('prettify-html', ALCHEMYST_FORMS__PLUGIN_URL . 'js/prettify.js');
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('alchemyst-forms-admin-js', ALCHEMYST_FORMS__PLUGIN_URL . 'js/admin.js', array('jquery', 'acejs', 'prettify-html', 'jquery-ui-sortable'), AF_SCRIPTVERSION);
        }

        if ($license->license_is_valid()) {
            $localize_args['datatables_settings'] = array(
                'colReorder' => array(
                    'realtime' => true,
                    'fixedColumnsLeft' => 2
                ),
                'order' => array(array( 0, "desc" )),
            );
        }
        else {
            $localize_args['datatables_settings'] = array(
                'colReorder' => array(
                    'realtime' => true,
                    'fixedColumnsLeft' => 2
                ),
                'order' => array(array( 0, "desc" )),
                'ordering' => false,
                'filtering' => false,
                'searching' => false,
                'paging' => false,
                'info' => false
            );
        }

        // Localize the admin js
        wp_localize_script('alchemyst-forms-admin-js', 'alchemyst_forms_admin_js', $localize_args);
    }

    public static function frontend_scripts() {
        $license = Alchemyst_Forms_License::get_instance();

        // Bootstrap styles
        if (file_exists(get_template_directory() . '/alchemyst-forms/bootstrap/css/bootstrap.css')) {
            wp_enqueue_style('alchemyst-forms-bootstrap', get_template_directory_uri() . '/alchemyst-forms/bootstrap/css/bootstrap.css', array(), AF_SCRIPTVERSION);
        }
        elseif (Alchemyst_Forms_Settings::get_setting('enable-bootstrap-styles') == "1") {
            wp_enqueue_style('alchemyst-forms-bootstrap', ALCHEMYST_FORMS__PLUGIN_URL . 'vendor/bootstrap/css/bootstrap.css', array(), AF_SCRIPTVERSION);
        }

        // Datepicker styles
        if (file_exists(get_template_directory() . '/alchemyst-forms/datepicker.css')) {
            wp_enqueue_style('alchemyst-forms-datepicker-css', get_template_directory_uri() . '/alchemyst-forms/datepicker.css', array('alchemyst-forms-bootstrap'), AF_SCRIPTVERSION);
        }
        else {
            wp_enqueue_style('alchemyst-forms-datepicker-css', ALCHEMYST_FORMS__PLUGIN_URL . 'css/datepicker.css', array('alchemyst-forms-bootstrap'), AF_SCRIPTVERSION);
        }

        // Google Recaptcha
        $recaptcha_site_key = Alchemyst_Forms_Settings::get_setting('recaptcha-site-key');
        $recaptcha_secret_key = Alchemyst_Forms_Settings::get_setting('recaptcha-secret-key');
        if ($recaptcha_site_key && $recaptcha_secret_key) { // Only need to include this if the settings are defined.
            wp_enqueue_script('recaptcha', 'https://www.google.com/recaptcha/api.js');
        }

        // Frontend Javascript. Contains functionality to deal with the form submissions, validation, etc.
        wp_enqueue_script('inputmask', ALCHEMYST_FORMS__PLUGIN_URL . 'js/inputmask/inputmask.min.js', array('jquery'));
        wp_enqueue_script('alchemyst-forms-frontend-js', ALCHEMYST_FORMS__PLUGIN_URL . 'js/frontend.js', array('jquery', 'inputmask'), AF_SCRIPTVERSION);

        // Datepicker
        wp_enqueue_script('jquery-ui-datepicker');

        // Localize some data.
        wp_localize_script('alchemyst-forms-frontend-js', 'alchemyst_forms_js', array(
            'ajax_url' => get_admin_url() . 'admin-ajax.php'
        ));
        wp_localize_script('alchemyst-forms-frontend-js', 'af_translations', array(
            'required_field' => Alchemyst_Forms_Messages::get_message('required-field'),
            'invalid_email' => Alchemyst_Forms_Messages::get_message('valid-email')
        ));
    }
}
// Let's go!
Alchemyst_Forms::init();
