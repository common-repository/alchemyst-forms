<?php
/**
 * Interpolator, designed to replace [name] shorttags in notifications, (and beyond?)
 */

class Alchemyst_Forms_Interpolator {

    /**
     * @param $arr - Array of messages to interpolate.
     * @param $form_id - Post ID of the contact form that we are interpolating for (get the $dom)
     * @param $request - Just a passthrough of $_REQUEST for one reason or another.
     */
    public static function interpolate_vars($arr, $form_id, $request, $dom) {
        $field_names = Alchemyst_Form::get_field_names($dom, $form_id);
        $encrypted_fields = Alchemyst_Form::get_encrypted_fields($form_id, $dom);

        $return = array();
        foreach ($arr as $key => $message) {
            $return[$key] = self::interpolate_message($form_id, $message, $request, $field_names, $dom, $encrypted_fields);
        }

        return $return;
    }

    /**
     * Do the interpolation.
     * TODO: Custom interpolation for non-$_REQUEST variables? Things like [form_id], [form_name], etc.
     */
    public static function interpolate_message($form_id, $message, $request, $field_names, $dom, $encrypted_fields) {
        $return_message = $message;

        foreach ($field_names as $field_name) {
            $field_type = Alchemyst_Form::get_field_type($field_name, $dom);

            if (in_array($field_name, $encrypted_fields)) {
                $request_value = "(Encrypted Value - Encrypted fields cannot be sent via email)";
            } else {
                $request_value = self::get_request_value($request[$field_name]);
            }

            if (strpos($return_message, '[' . $field_name . ']') === false) {
                $field_name = str_replace('_', ' ', $field_name);
            }

            $field_type = Alchemyst_Form::get_field_type($field_name, $dom);

            if ($field_type == 'wysiwyg') {
                $return_message = str_replace('[' . $field_name . ']', $request_value, $return_message);
            }
            else {
                $return_message = str_replace('[' . $field_name . ']', nl2br(htmlentities($request_value)), $return_message);
            }

        }

        // Do the other interpolations
        $return_message = str_replace('[alchemyst-forms-referrer]', $request['_alchemyst-forms-referrer'], $return_message);

        return $return_message;
    }

    /**
     * Just a small helper to keep interpolate_message clean.
     *
     * @param $r - Request value. Coule be an array, or a string, or pretty much anything.
     * @return a string representation of $r, regardless of what type of variable $r actually was.
     */
    public static function get_request_value($r) {
        if ($r) {
            $r_string = '';
            if (is_array($r)) {
                $r_string = Alchemyst_Forms_Utils::array_to_string($r);
            }
            elseif (is_string($r)) {
                $r_string = $r;
            }
            return $r_string;
        }
        else {
            return '';
        }
    }
}
