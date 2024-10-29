<?php
/**
 * Provides a basic messages router that allows for customization, and interpolation of system error messages
 * particularly those that may appear on the front end of the website.
 */

class Alchemyst_Forms_Messages {

    // Main message router
    public static function get_message($message_name, $form_id = 0, $interpolations = array()) {
        switch ($message_name) {
            case 'success':
                return self::get_success_message($form_id);
                break;

            case 'min-length':
                return self::get_min_length_message($interpolations);
                break;

            case 'max-length':
                return self::get_max_length_message($interpolations);
                break;

            case 'recaptcha':
                return Alchemyst_Forms_Settings::get_setting('default-recaptcha-message');
                break;

            case 'matching-field':
                return self::get_matching_field_message($interpolations);
                break;

            case 'required-field':
                return self::get_required_field_message($interpolations);
                break;

            case 'valid-email':
                return Alchemyst_Forms_Settings::get_setting('default-valid-email-message');
                break;

            case 'configuration-error':
                return Alchemyst_Forms_Settings::get_setting('default-configuration-error-message');
                break;

            case 'generic-file-error':
                return Alchemyst_Forms_Settings::get_setting('default-generic-file-error-message');
                break;

            case 'file-type-error':
                return self::get_file_type_error_message($form_id, $interpolations);
                break;

            case 'file-size-error':
                return self::get_file_size_error_message($form_id, $interpolations);
                break;

            case 'file-image-size-error':
                return self::get_file_image_size_error_message($form_id, $interpolations);
                break;

            default:
                return '';
                break;
        }
    }

    public static function get_success_message($form_id) {
        $form_message = get_post_meta($form_id, '_af-success-message', true);

        if (!$form_message) {
            $form_message = Alchemyst_Forms_Settings::get_setting('default-success-message');
        }

        if (!$form_message) $message = Alchemyst_Forms_Settings::get_default('default-success-message');

        return $form_message;
    }

    public static function get_min_length_message($interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-min-length-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-min-length-message');
        return self::interpolate($message, $interpolations);
    }

    public static function get_max_length_message($interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-max-length-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-max-length-message');
        return self::interpolate($message, $interpolations);
    }

    public static function get_matching_field_message($interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-matching-field-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-matching-field-message');
        return self::interpolate($message, $interpolations);
    }

    public static function get_required_field_message($interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-required-field-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-required-field-message');
        return self::interpolate($message, $interpolations);
    }

    public static function get_file_type_error_message($form_id, $interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-file-type-error-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-file-type-error-message');
        return self::interpolate($message, $interpolations);
    }

    public static function get_file_size_error_message($form_id, $interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-file-size-error-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-file-size-error-message');
        return self::interpolate($message, $interpolations);
    }

    public static function get_file_image_size_error_message($form_id, $interpolations) {
        $message = Alchemyst_Forms_Settings::get_setting('default-file-image-size-error-message');
        if (!$message) $message = Alchemyst_Forms_Settings::get_default('default-file-image-size-error-message');
        return self::interpolate($message, $interpolations);
    }

    // Simple interpolation.
    public static function interpolate($message, $interpolations) {
        foreach ($interpolations as $name => $value) {
            $message = str_replace('[' . $name . ']', $value, $message);
        }
        return $message;
    }

    // Interpret field names to be displayed in validation messages. Also implemented in frontend.js
    public static function interpret_name_in_message($name) {
        $name = rtrim($name, '[]');
        $name = str_replace('-', ' ', $name);
        $name = str_replace('_', ' ', $name);
        $name = ucwords($name);
        $name = '"' . $name . '"';
        return $name;
    }
}
