<?php
/**
 * Interpolate curly shorttags "{tag}" for frontend display.
 * Useful for things like recaptcha integration.
 */

class Alchemyst_Form_Interpolator {

    function __construct($html) {
        $this->html = $html;
    }

    /**
     * Interpolate with all methods in the $form_interpolator_class
     * This class can be extended and applied back with a filter to provide additional (or overwrite) interpolation methods.
     */
    function interpolate() {
        $result = $this->html;
        $form_interpolator_class = apply_filters('alchemyst-forms:interpolator-class', 'Alchemyst_Form_Interpolator_Methods');
        foreach (get_class_methods($form_interpolator_class) as $method) {
            $result = call_user_func(array($form_interpolator_class, $method), $this->html);
        }
        return $result;
    }
}

/**
 * Default validator methods for notifications
 * Written in such a way that all class methods within this class are called.
 *
 * Can be extended:
 * class My_Alchemyst_Form_Interpolator_Methods extends Alchemyst_Form_Interpolator_Methods { *your new methods* }
 * add_filter('alchemyst-forms:interpolator-class', 'My_Alchemyst_Form_Interpolator_Methods');
 *
 * All functions are passed the following
 * @param $html - The output HTML for the contact form. Will contain {short-tag}s
 */
class Alchemyst_Form_Interpolator_Methods {

    public static function recaptcha($html) {
        $recaptcha_site_key = Alchemyst_Forms_Settings::get_setting('recaptcha-site-key');
        $recaptcha_secret_key = Alchemyst_Forms_Settings::get_setting('recaptcha-secret-key');
        if ($recaptcha_site_key && $recaptcha_secret_key) {
            return str_replace('{recaptcha}', '<div class="g-recaptcha" data-sitekey="' . $recaptcha_site_key .'"></div>', $html);
        }
        else {
            return str_replace('{recaptcha}', '<div class="alert alert-danger"><p><strong>Error:</strong> This form tried to render a reCAPTCHA but the reCAPTCHA settings are not properly configured.</p></div>', $html);
        }
    }
}
