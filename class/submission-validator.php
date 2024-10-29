<?php
/**
 * Basic form validator class.
 *
 * Set up the required, matching, min_lengths, and max_lengths in object construction, then validate as much data as you
 * would like to with validate_postdata($_POST/$_REQUEST)
 */
class Alchemyst_Forms_Submission_Validator {
    private $required_field_keys;
    private $matching_field_keys;
    private $min_field_lengths;
    private $max_field_lengths;
    private $dom;

    function __construct($required_keys = array(), $matching_keys = array(), $min_lengths = array(), $max_lenghts = array(), $dom = false) {
        if (is_array($required_keys))
            $this->required_field_keys = $required_keys;
        if (is_array($matching_keys))
            $this->matching_field_keys = $matching_keys; // $key => $val (associative, both should be postdata keys and should match)
        if (is_array($min_lengths))
            $this->min_field_lengths = $min_lengths;
        if (is_array($max_lenghts))
            $this->max_field_lengths = $max_lenghts;

        $this->dom = $dom;
    }

    public function set_required_keys($required_keys) {
        if (is_array($required_keys))
            $this->required_field_keys = $required_keys;
    }

    public function set_matching_keys($matching_keys) {
        if (is_array($matching_keys))
            $this->matching_field_keys = $matching_keys;
    }

    public function set_min_lengths($min_lengths) {
        if (is_array($min_lengths))
            $this->min_field_lengths = $min_lengths;
    }

    public function set_max_lengths($max_lenghts) {
        if (is_array($max_lenghts))
            $this->max_field_lengths = $max_lenghts;
    }

    public function validate_postdata($post) {
        $valid = true;
        foreach ($post as $key => $val) {

            $input_elem = $this->dom->find('[name="' . $key . '"]');

            if (count($input_elem)) {
                $type = $input_elem[0]->type;

                if ($type == "email") {
                    if (!$this->validate_email($val)) {
                        $valid = array(
                            'field' => Alchemyst_Forms_Messages::interpret_name_in_message($key),
                            'message' => Alchemyst_Forms_Messages::get_message('valid-email')
                        );
                    }
                }
            }

            if (in_array($key, $this->required_field_keys)) {
                if (empty($val)) {
                    $valid = array(
                        'field' => Alchemyst_Forms_Messages::interpret_name_in_message($key),
                        'message' => Alchemyst_Forms_Messages::get_message('required-field', false, array(
                            'field' => $this->unslugify($key),
                        ))
                    );
                }
            }

            if (array_key_exists($key, $this->matching_field_keys)) {
                if ($post[$this->matching_field_keys[$key]] != $val) {
                    $valid = array(
                        'field' => Alchemyst_Forms_Messages::interpret_name_in_message($key),
                        'message' => Alchemyst_Forms_Messages::get_message('matching-field', false, array(
                            'field' => $this->unslugify($key),
                            'match' => $this->min_field_lengths[$key]
                        ))
                    );
                }
            }


            if (array_key_exists($key, $this->min_field_lengths)) {
                // If val is less than the required length and its required, :OR: if val is less than the required length, its not empty, and its not required.
                if (strlen($val) < $this->min_field_lengths[$key] && in_array($key, $this->required_field_keys)
                || (!in_array($key, $this->required_field_keys) && strlen($val) < $this->min_field_lengths[$key] && !empty($val))) {
                    $valid = array(
                        'field' => Alchemyst_Forms_Messages::interpret_name_in_message($key),
                        'message' => Alchemyst_Forms_Messages::get_message('min-length', false, array(
                            'field' => $this->unslugify($key),
                            'length' => $this->min_field_lengths[$key]
                        ))
                    );
                }
            }

            if (array_key_exists($key, $this->max_field_lengths)) {
                if (strlen($val) > $this->max_field_lengths[$key]) {
                    $valid = array(
                        'field' => Alchemyst_Forms_Messages::interpret_name_in_message($key),
                        'message' => Alchemyst_Forms_Messages::get_message('max-length', false, array(
                            'field' => $this->unslugify($key),
                            'length' => $this->min_field_lengths[$key]
                        ))
                    );
                }
            }

            if ($key == 'g-recaptcha-response') {
                if (!self::validate_recaptcha($post[$key])) {
                    $valid = array(
                        'field' => Alchemyst_Forms_Messages::interpret_name_in_message($key),
                        'message' => Alchemyst_Forms_Messages::get_message('recaptcha')
                    );
                }
            }
        }

        return $valid;
    }

    private function validate_email($email) {
        if (filter_var($email, FILTER_VALIDATE_EMAIL))
            return true;
        else
            return false;
    }

    // TODO: Remove this, refactor file to use Alchemyst_Forms_Utils::unslugify();
    private function unslugify($key) {
        return ucwords(str_replace('_', ' ', str_replace('-', ' ', $key)));
    }

    /**
     * Validate a recaptcha response. Assumes that Alchemyst_Forms_Settings::get_setting('recaptcha-secret-key') will work.
     */
    public static function validate_recaptcha($response) {
        try {
            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = array(
                'secret'   => Alchemyst_Forms_Settings::get_setting('recaptcha-secret-key'),
                'response' => $response,
                'remoteip' => $_SERVER['REMOTE_ADDR']
            );

            $options = array(
                'http' => array(
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                )
            );

            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            return json_decode($result)->success;
        }
        catch (Exception $e) {
            return null;
        }
    }
}
