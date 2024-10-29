<?php
/**
 * This collection of classes is designed to validate the actual form HTML from the editor. NOT the client side.
 * Client side validation is handled in TODO:implement
 */
use PHPHtmlParser\Dom;

class Alchemyst_Forms_Validator {
    const LEVEL_SUCCESS = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;

    function __construct($post_id) {
        $this->post_id = $post_id;
        $this->cform_code = get_post_meta($post_id, '_alchemyst_forms_contact-form-code', true);
        $dom = new Dom;
        $this->dom = $dom->load($this->cform_code);
        $this->notifications = Alchemyst_Forms_Notifications::get_notifications($post_id);
    }

    /**
     * Validate with all methods in the $validator_methods_class
     * This class can be extended and applied back with a filter to provide additional (or overwrite) field validation
     */
    function validate() {
        $validator_methods_class = apply_filters('alchemyst-forms:validator-class', 'Alchemyst_Forms_Validator_Methods');

        $responses = array();
        foreach (get_class_methods($validator_methods_class) as $method) {
            $responses[] = call_user_func(array($validator_methods_class, $method), $this->post_id, $this->cform_code, $this->dom);
        }

        // Notification validators are able to be implemented purely through filters and actions. This validator framework
        // Allows for that to hapen.
        $notification_validators = array(
            'email' => 'Alchemst_Forms_Notification_Validator_Methods',
        );
        $notification_validators = apply_filters('alchemyst_forms:form-notification-validator-classes', $notification_validators);
        foreach ($this->notifications as $notification) {
            if (!isset($notification_validators[$notification->type])) continue;

            foreach (get_class_methods($notification_validators[$notification->type]) as $method) {
                $responses[] = call_user_func(array($notification_validators[$notification->type], $method), $this->post_id, $notification);
            }
        }

        if (empty($this->notifications)) {
            $show_no_notification_warning = apply_filters('alchemyst_forms:show-no-notification-warning', true);

            if ($show_no_notification_warning) {
                $responses[] = Alchemyst_Forms_Validator::build_response(false, 'Your form has no notifications. Without any notifications, your form will only save responses to the Entries database.', self::LEVEL_WARNING);
            }
        }

        return $responses;
    }

    /**
     * Response builder, just keeps code cleaner.
     */
    public static function build_response($valid, $message = '', $level = 0, $dismissable = false) {
        return array(
            'valid' => $valid,
            'message' => $message,
            'level' => $level,
            'level_str' => Alchemyst_Forms_Validator::level_as_string($level),
            'dismissable' => $dismissable
        );
    }

    /**
     * Translate constant into string.
     */
    public static function level_as_string($level) {
        switch ($level) {
            case 0:
                return 'success';
            case 1:
                return 'info';
            case 2:
                return 'warning';
            case 3:
                return 'error';
            default:
                return 'error';
        }
    }
}

class Alchemyst_Forms_Validator_Methods_Type {
    const LEVEL_SUCCESS = 0;
    const LEVEL_INFO = 1;
    const LEVEL_WARNING = 2;
    const LEVEL_ERROR = 3;
}

/**
 * Default validator methods.
 * Written in such a way that all class methods within this class are called.
 *
 * Can be extended:
 * class My_Alchemyst_Forms_Validator_Methods extends Alchemyst_Forms_Validator_Methods { *your new methods* }
 * add_filter('_alchemyst-forms:validator-class', 'My_Alchemyst_Forms_Validator_Methods');
 *
 * All functions are passed the following
 * @param $post_id - The post_id associated with this contact form.
 * @param $cform_code - The raw HTML for the contact form.
 * @param $dom - The loaded Dom object with paquettg/PHPHtmlParser
 */
class Alchemyst_Forms_Validator_Methods extends Alchemyst_Forms_Validator_Methods_Type {

    /**
     * No <form> should exist in the dom
     */
    public static function no_form_tag_allowed($post_id, $cform_code, $dom) {
        $f = $dom->find('form');
        if (count($f))  {
            return Alchemyst_Forms_Validator::build_response(false, 'Your form contains a <code>&lt;form&gt;</code> tag. Please remove the form tag in order to ensure your form works as expected.', self::LEVEL_ERROR);
        }
        else {
            return Alchemyst_Forms_Validator::build_response(true);
        }
    }

    /**
     * A submit button should be present in the dom
     */
    public static function require_submit_button($post_id, $cform_code, $dom) {
        $i = $dom->find('input[type="submit"]');
        if (!count($i))  {
            return Alchemyst_Forms_Validator::build_response(false, 'Your form does not contain a submit button. No one will be able to submit your form without one!', self::LEVEL_ERROR);
        }
        else {
            return Alchemyst_Forms_Validator::build_response(true);
        }
    }

    /**
     * Input name="_alchemyst-forms-referrer" is reserved.
     */
    public static function disallowed_name_attributes($post_id, $cform_code, $dom) {
        $disallowed_names = array(
            '_alchemyst-forms-referrer',
            'form_id',
            '_wpnonce',
            'action',
            '_af-entry-created-date',
            '_af-entry-id',
            'g-recaptcha-response'
        );

        foreach ($disallowed_names as $disallowed) {
            $i = $dom->find('[name="' . $disallowed . '"]');
            if (count($i)) {
                return Alchemyst_Forms_Validator::build_response(false, 'Your form contains an input with <code>name=&quot;' . $disallowed . '&quot;</code>. This input name is reserved for the plugin and could prevent things from working as expected. Please remove or rename this input.', self::LEVEL_ERROR);
            }
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    /**
     * Inputs, selects, and textareas should all have name attributes (except submit buttons)
     */
    public static function require_name_attributes($post_id, $cform_code, $dom) {
        $inputs = $dom->find('input');
        foreach ($inputs as $input) {
            if (!$input->name && $input->type != "submit" && $input->type != "button") {
                return Alchemyst_Forms_Validator::build_response(false, 'Your form contains an <code>&lt;input&gt;</code> tag with no <code>name</code> attribute.', self::LEVEL_WARNING);
            }
        }

        $textareas = $dom->find('textarea');
        foreach ($textareas as $textarea) {
            if (!$textarea->name) {
                return Alchemyst_Forms_Validator::build_response(false, 'Your form contains a <code>&lt;textarea&gt;</code> tag with no <code>name</code> attribute.', self::LEVEL_WARNING);
            }
        }

        $selects = $dom->find('select');
        foreach ($selects as $select) {
            if (!$select->name) {
                return Alchemyst_Forms_Validator::build_response(false, 'Your form contains a <code>&lt;select&gt;</code> tag with no <code>name</code> attribute.', self::LEVEL_WARNING);
            }
        }

        $repeats = $dom->find('repeatable');
        foreach ($repeats as $repeat) {
            if (!$repeat->name) {
                return Alchemyst_Forms_Validator::build_response(false, 'Your form contains a <code>&lt;repeatable&gt;</code> tag with no <code>name</code> attribute.', self::LEVEL_WARNING);
            }
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    public static function check_duplicates($post_id, $cform_code, $dom) {
        $fields = $dom->find('[name]');
        $names = array();
        foreach ($fields as $field) {
            $name = strtolower($field->getAttribute('name'));
            $type = strtolower($field->getAttribute('type'));

            if ($type == 'radio' || $type == 'checkbox') continue;

            if (in_array($name, $names)) {

                if (!preg_match("/\[\]/", $name)) {
                    return Alchemyst_Forms_Validator::build_response(false, 'Your form appears to contain two fields which contain the same name <code>'.$name.'</code>. With duplicate field names that are not array names (containing <code>[]</code> at the end of the name), only the last field will be used to send entry data.', self::LEVEL_WARNING);
                }
            }
            $names[] = $name;
        }


        return Alchemyst_Forms_Validator::build_response(true);
    }

    public static function verify_checkboxes($post_id, $cform_code, $dom) {
        $fields = $dom->find('[name]');
        $names = array();
        foreach ($fields as $field) {
            $name = strtolower($field->getAttribute('name'));
            $type = strtolower($field->getAttribute('type'));


            if (in_array($name, $names)) {

                if (!preg_match("/\[\]/", $name)) {
                    return Alchemyst_Forms_Validator::build_response(false, 'Your form appears to contain two checkbox fields which contain the same name <code>'.$name.'</code>. In order to use checkboxes as a list, you should append <code>[]</code> to the end of the name attribute (e.g., "myfield" becomes "myfield[]").', self::LEVEL_WARNING);
                }
            }
            $names[] = $name;
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    public static function validate_wysiwygs($post_id, $cform_code, $dom) {
        $wysiwygs = $dom->find('input[type="wysiwyg"]');

        if (count($wysiwygs)) {
            $license = Alchemyst_Forms_License::get_instance();
            if (!$license->license_is_valid()) return Alchemyst_Forms_Validator::build_response(false, 'You must purchase a license to use <code>&lt;input type=&quot;wysiwyg&quot;&gt;</code> inputs in your forms.', self::LEVEL_ERROR);
        }

        foreach ($wysiwygs as $wysiwyg) {
            $name = $wysiwyg->getAttribute('name');

            if (empty($name) || preg_match("/[^a-z_]/", $name)) {
                return Alchemyst_Forms_Validator::build_response(false, 'Your form contains a WYSIWYG input with an invalid name <code>'.$name.'</code>. WYSIWYG fields use <code>wp_editor()</code> and as such are restricted to lower case letters and underscores in the name attribute only.', self::LEVEL_ERROR);
            }
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    public static function nested_repeatables($post_id, $cform_code, $dom) {
        $nested = $dom->find('repeatable repeatable');
        if (count($nested)) {
            return Alchemyst_Forms_Validator::build_response(false, 'Nesetd <code>&lt;repeatable&gt;</code> tags are not supported at this time.', self::LEVEL_ERROR);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    /**
     * reCAPTCHA
     */
    public static function valid_recaptcha($post_id, $cform_code, $dom) {
        if (strpos($cform_code, '{recaptcha}') === false)
            return Alchemyst_Forms_Validator::build_response(true);


        $recaptcha_site_key = Alchemyst_Forms_Settings::get_setting('recaptcha-site-key');
        $recaptcha_secret_key = Alchemyst_Forms_Settings::get_setting('recaptcha-secret-key');

        if (!$recaptcha_site_key|| !$recaptcha_secret_key) {
            return Alchemyst_Forms_Validator::build_response(false, 'Your form contains the <code>{recaptcha}</code> tag, but it looks like your <a href="' . admin_url() . 'edit.php?post_type=alchemyst-forms&page=alchemyst-forms-settings#recaptcha-settings">reCAPTCHA settings</a> are not properly configured. Without this, reCAPTCHA will not function as expected', self::LEVEL_ERROR);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    /**
     * Address field valid? Not if they didn't provide an API key or its not enabled.
     */
    public static function valid_address_fields($post_id, $cform_code, $dom) {
        $address_fields = $dom->find('input[type="address"]');

        if (count($address_fields)) {
            $license = Alchemyst_Forms_License::get_instance();
            if (!$license->license_is_valid()) return Alchemyst_Forms_Validator::build_response(false, 'You must purchase a license to use <code>&lt;input type=&quot;address&quot;&gt;</code> inputs in your forms.', self::LEVEL_ERROR);
        }

        // If there are address fields, and the use-address-input setting is not turned on,
        // or the google-maps-api-key setting was left blank, show the error.
        $gmaps_api_key = Alchemyst_Forms_Settings::get_setting('google-maps-api-key');
        if (count($address_fields) && (Alchemyst_Forms_Settings::get_setting('use-address-input') != "1" || empty($gmaps_api_key))) {

            return Alchemyst_Forms_Validator::build_response(false, 'Your form contains an <code>&lt;input type=&quot;address&quot;&gt;</code> tag, but it looks like your <a href="' . admin_url() . 'edit.php?post_type=alchemyst-forms&page=alchemyst-forms-settings#address-input-settings">Address Input Settings</a> are not properly configured. Without this, address inputs will not function as expected', self::LEVEL_ERROR);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    /**
     * File uploads not allowed in free version.
     */
    public static function file_uploads_allowed($post_id, $cform_code, $dom) {
        $file_fields = $dom->find('input[type="file"]');

        if (count($file_fields)) {
            $license = Alchemyst_Forms_License::get_instance();
            if (!$license->license_is_valid()) return Alchemyst_Forms_Validator::build_response(false, 'You must purchase a license to use <code>&lt;input type=&quot;file&quot;&gt;</code> inputs in your forms.', self::LEVEL_ERROR);
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }
}


/**
 * Default validator methods for notifications
 * Written in such a way that all class methods within this class are called.
 *
 * Can be extended:
 * class My_Alchemyst_Forms_Validator_Methods extends Alchemyst_Forms_Validator_Methods { *your new methods* }
 * add_filter('alchemyst-forms:validator-class', 'My_Alchemyst_Forms_Validator_Methods');
 *
 * All functions are passed the following
 * @param $post_id - The post_id associated with this contact form.
 * @param $notifications - An array of Alchemst_Forms_Notification objects
 */
class Alchemst_Forms_Notification_Validator_Methods extends Alchemyst_Forms_Validator_Methods_Type {

    public static function valid_to_address($post_id, $notification) {


        if (empty($notification->to)) {
            return Alchemyst_Forms_Validator::build_response(false, 'No to email address was specified for notification (ID: ' . $notification->ID . '). You will need to specify a to email address to ensure proper deliver of this notification.', self::LEVEL_ERROR);
        }

        $addresses = explode(',', $notification->to);
        foreach ($addresses as &$address) {
            $address = trim($address);
        }

        foreach ($addresses as $address) {
            if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
                if (!preg_match('/\[(.*)\]/', $address)) {
                    return Alchemyst_Forms_Validator::build_response(false, 'A (to) email address (' . $address . ') in a notification (ID: ' . $notification->ID . ') looks invalid and may cause some unexpected behavior.', self::LEVEL_WARNING);
                }
            }
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }

    public static function valid_from_address($post_id, $notification) {

        $address = $notification->from;

        if (empty($address)) {
            return Alchemyst_Forms_Validator::build_response(false, 'No from email address was specified for notification (ID: ' . $notification->ID . '). You will need to specify a from email address to ensure proper deliver of this notification.', self::LEVEL_ERROR);
        }

        if (!filter_var($address, FILTER_VALIDATE_EMAIL)) {
            if (!preg_match('/\[(.*)\]/', $address)) {
                return Alchemyst_Forms_Validator::build_response(false, 'A (from) email address (' . $address . ') in a notification (ID: ' . $notification->ID . ') looks invalid and may cause some unexpected behavior.', self::LEVEL_WARNING);
            }
        }

        return Alchemyst_Forms_Validator::build_response(true);
    }
}
