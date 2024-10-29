<?php
/**
 * Adds the settings page
 * Adds utility functions to wrap around the Wordpress site options API.
 */

class Alchemyst_Forms_Settings {
    // List of settings. These are nested in functions because constants cannot be defined as arrays.
    // Saved to the site_options meta table as _alchemyst_forms-{setting-name}
    public static function settings() {
        $settings = array(
            'pro-license-key',
            'default-success-message',
            'default-min-length-message',
            'default-max-length-message',
            'default-recaptcha-message',
            'default-matching-field-message',
            'default-required-field-message',
            'default-valid-email-message',
            'default-configuration-error-message',
            'default-generic-file-error-message',
            'default-file-type-error-message',
            'default-file-size-error-message',
            'default-file-image-size-error-message',
            'default-recaptcha-message',
            'editor-syntax',
            'editor-preferred-line-length',
            'editor-font-size',
            'use-smtp',
            'smtp-host',
            'smtp-port',
            'smtp-username',
            'smtp-password',
            'recaptcha-site-key',
            'recaptcha-secret-key',
            'upload-max-file-size',
            'upload-allowable-file-types',
            'upload-max-image-width',
            'upload-max-image-height',
            'use-tel-input',
            'tel-mask',
            'use-address-input',
            'google-maps-api-key',
            'enable-bootstrap-styles'
        );
        return apply_filters('alchemyst_forms:settings', $settings);
    }

    // Of the above settings, which should be encrypted for storage?
    // Encryption and decryption is not very speedy, so recommend keeping this slim, typically passwords, or API keys.
    public static function encrypted_settings() {
        $settings = array(
            'smtp-password',
            'recaptcha-secret-key'
        );
        return apply_filters('alchemyst_forms:encrypted-settings', $settings);
    }

    public static function pro_settings() {
        $settings = array(
            'default-success-message',
            'default-min-length-message',
            'default-max-length-message',
            'default-recaptcha-message',
            'default-matching-field-message',
            'default-required-field-message',
            'default-valid-email-message',
            'default-configuration-error-message',
            'default-generic-file-error-message',
            'default-file-type-error-message',
            'default-file-size-error-message',
            'default-file-image-size-error-message',
            'editor-syntax',
            'editor-preferred-line-length',
            'editor-font-size',
            'use-smtp',
            'smtp-host',
            'smtp-port',
            'smtp-username',
            'smtp-password',
            'upload-max-file-size',
            'upload-allowable-file-types',
            'upload-max-image-width',
            'upload-max-image-height',
            'use-tel-input',
            'tel-mask',
            'use-address-input',
            'google-maps-api-key'
        );
        return $settings;
    }

    /**
     * Admin interface for site settings.
     */
    public static function init() {
        self::add_menu_page();
    }

    public static function add_menu_page() {
        $menu_function = function() {
            $page_title = "Alchemyst Forms Settings";
            $menu_title = "Settings";
            $capability = "af-manage-settings";
            $menu_slug = "alchemyst-forms-settings";
            $function = array(__CLASS__, 'callback');
            add_submenu_page('edit.php?post_type=' . AF_POSTTYPE, $page_title, $menu_title, $capability, $menu_slug, $function);
        };
        add_action('admin_menu', $menu_function);
    }

    public static function callback() {
        // Update options if $_POST.
        if ($_POST) {
            foreach (self::settings() as $setting) {
                if (!isset($_POST[$setting])) continue;
                self::update_setting($setting, $_POST[$setting]);
            }
        }
        // Load the view.
        echo Alchemyst_Forms_Utils::load_admin_template('settings');
    }


    /**
     * Site options wrapper. Gives us one unified interface to do this from, we can control defaults here really easily
     * Has the advantage that if we want to change a default option we only have to do it in one place
     */
    public static function get_setting($setting_name) {
        $default = self::get_default($setting_name);
        if (in_array($setting_name, self::encrypted_settings())) {
            $v = get_site_option('_alchemyst_forms-' . $setting_name, $default);

            if ($v) {
                try {
                    return Alchemyst_Forms_Utils::decrypt($v);
                }
                catch (Exception $e) {

                }
            }
            else
                return $v;
        }
        else {
            return get_site_option('_alchemyst_forms-' . $setting_name, $default);
        }
    }

    /**
     * Get default value for a site option defined by this plugin.
     * Registers a filter named - alchemyst_forms:setting-default:setting_name
     *    So you can do add_filter('alchemyst_forms:settting-default:editor-syntax', 'monokai'); if you want...
     *
     * Any settings not mentioned here are defaulted to an empty string ('')
     */
    public static function get_default($setting_name) {
        $default = '';
        switch ($setting_name) {
            case 'default-success-message':
                $default = "Your message has been successfully received. Thank you.";
                break;

            case 'default-min-length-message':
                $default = 'The [field] field must be at least [length] characters long';
                break;

            case 'default-max-length-message':
                $default = 'The [field] field cannot be more than [length] characters long';
                break;

            case 'default-recaptcha-message':
                $default = 'You must authenticate with reCAPTCHA to submit this form';
                break;

            case 'default-matching-field-message':
                $default = 'The [field] field must match the [matching] field.';
                break;

            case 'default-required-field-message':
                $default = 'Required field [field] is empty.';
                break;

            case 'default-valid-email-message':
                $default = 'Email format does not appear to be valid.';
                break;

            case 'default-configuration-error-message':
                $default = 'Something went wrong with your submission. This is likely a configuration error with this form.';
                break;

            case 'default-generic-file-error-message':
                $default = 'Something went wrong with your file upload. If this issue persists please contact us to let us know.';
                break;

            case 'default-file-type-error-message':
                $default = 'Your file [filename] is a disallowed file type. You must provide one of the following filetypes: [allowable_types]';
                break;

            case 'default-file-size-error-message':
                $default = 'Your file [filename] is larger than the maximum allowable file size of [size]';
                break;

            case 'default-file-image-size-error-message':
                $default = 'Your file [filename] is larger than the maximum allowed dimensions for this file upload. Maximum width: [width]px, Maximum height: [height]px';
                break;

            case 'editor-syntax':
                $default = 'chrome';
                break;

            case 'editor-preferred-line-length':
                $default = 120;
                break;

            case 'editor-font-size':
                $default = 14;
                break;

            case 'use-smtp':
                $default = '0';
                break;

            case 'smtp-port':
                $default = 25;
                break;

            case 'upload-max-file-size':
                $default = 7340032;
                break;

            case 'upload-allowable-file-types':
                $default = 'jpg,png,gif,pdf,doc,docx,key,ppt,pptx,pps,ppsx,odt,xls,xlsx,zip';
                break;

            case 'upload-max-image-width':
                $default = 10000;
                break;

            case 'upload-max-image-height':
                $default = 10000;
                break;

            case 'use-tel-input':
                $default = '1';
                break;

            case 'tel-mask':
                $default = '(999) 999-9999';
                break;

            case 'use-address-input':
                $default = '0';
                break;

            case 'enable-bootstrap-styles':
                $default = '1';
                break;

            default:
                $default = '';
                break;
        }

        return apply_filters('alchemyst_forms:setting-default:' . $setting_name, $default);
    }

    /**
     * Update a setting, simple wrapper for update_site_option. Will encrypt as necessary.
     *
     * @param $setting_name - Name of the setting to update. Saves prefixed in database.
     *                        Note that names are static and referenced in other functions.
     * @param $value - Value to save for this setting. Will be encrypted if present in
      *                Alchemyst_Forms_Settings::encrypted_settings()
     */
    public static function update_setting($setting_name, $value) {
        $license = Alchemyst_Forms_License::get_instance();
        $default = self::get_default($setting_name);

        // Don't update pro settings if we're not in pro mode. will preserve database.
        if (in_array($setting_name, self::pro_settings()) && !$license->license_is_valid() && $setting_name != 'pro-license-key') {
            return false;
        }

        do_action('alchemyst_forms:pre-update-setting:' . $setting_name, $value);

        if (in_array($setting_name, self::encrypted_settings())) {
            update_site_option('_alchemyst_forms-' . $setting_name, Alchemyst_Forms_Utils::encrypt($value));
        }
        else {
            update_site_option('_alchemyst_forms-' . $setting_name, $value);
        }
    }
}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Settings', 'init'));
