<?php
/**
 * SMTP Relay using PHPMailer with the SMTP class.
 */
class Alchemyst_Forms_Elastic_Email {

    public static function content_type() {
        return 'text/html';
    }

    public static function send($to, $from, $from_name, $subject, $body_html, $cc = array(), $bcc = array(), $attachments = array(), $is_smtp = true) {

        if ($is_smtp) {

            add_action( 'phpmailer_init', function($phpmailer) {
                $phpmailer->isSMTP();
                $phpmailer->Host = Alchemyst_Forms_Settings::get_setting('smtp-host');
                $phpmailer->SMTPAuth = true; // Force it to use Username and Password to authenticate
                $phpmailer->Port = Alchemyst_Forms_Settings::get_setting('smtp-port');
                $phpmailer->Username = Alchemyst_Forms_Settings::get_setting('smtp-username');
                $phpmailer->Password = Alchemyst_Forms_Settings::get_setting('smtp-password');
            });
        }

        $headers = array();

        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = "From: {$from_name} <{$from}>";

        if (count($cc) && is_array($cc)) {
            foreach ($cc as $c) {
                $headers[] = "Cc: " . $c;
            }
        }

        if (count($bcc) && is_array($bcc)) {
            foreach ($bcc as $c) {
                $headers[] = "Bcc: " . $c;
            }
        }

        $res = wp_mail($to, $subject, $body_html, $headers, $attachments); // FAILING

        return $res;
    }
}

/**
 * Basic email class intended to simplify sending templated HTML emails.
 * Templates belong in an 'email-templates' directory in the same location as this file.
 */
class Alchemyst_Forms_Email {
    private $template;
    private $content;
    private $heading;
    private $subject;
    private $to;
    private $cc;
    private $bcc;
    private $from;
    private $from_name;
    private $headers;
    private $attachments;

    function __construct($template = 'default.php') {
        $this->template = $template;
        $this->headers  = 'MIME-Version: 1.0' . "\r\n";
        $this->headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $this->cc = array();
        $this->bcc = array();
    }

    public function to($to) {
        if (!empty($to)) {
            if (!is_array($to)) {
                $addresses = explode(',', $to);
                $addresses = array_map('trim', $addresses);
                $this->to = $addresses;
            }
            else {
                $this->to = $to;
            }
        }
        else
            throw new Exception('No email address specified');
    }

    public function cc($cc) {
        if (!is_array($cc) && !empty($cc)) {
            $addresses = explode(',', $cc);
            $addresses = array_map('trim', $addresses);
            $this->cc = $addresses;
        }
        else
            $this->cc = $cc;
    }

    public function bcc($bcc) {
        if (!is_array($bcc) && !empty($bcc)) {
            $addresses = explode(',', $bcc);
            $addresses = array_map('trim', $addresses);
            $this->bcc = $addresses;
        }
        else
            $this->bcc = $bcc;
    }

    public function from($from) {
        if (!empty($from))
            $this->from = $from;
        else
            throw new Exception('Email Error: No email address specified');
    }

    public function from_name($from_name) {
        if (!empty($from_name))
            $this->from_name = $from_name;
        else
            throw new Exception('Email Error: No from name specified specified');
    }

    public function subject($subject) {
        if (!empty($subject))
            $this->subject = $subject;
        else
            throw new Exception('Email Error: No subject specified');
    }

    public function heading($heading = '') {
        $this->heading = $heading;
    }

    public function attachments($attachments = array()) {
        if (is_array($attachments))
            $this->attachments = $attachments;
        else
            throw new Exception('Email Error: Attachments must be provided as an array');
    }

    public function content($content = '') {
        $this->content = $content;
    }

    public function headers($headers) {
        if (!empty($headers))
            $this->headers = $headers;
        else
            throw new Exception('Email Error: No headers specified');
    }

    public function send() {
        $loaded_template = Alchemyst_Forms_Utils::load_frontend_template('email/' . $this->template, false);
        $loaded_template = str_replace('{heading}', $this->heading, $loaded_template);
        $loaded_template = str_replace('{content}', $this->content, $loaded_template);

        $this->headers .= 'From: ' . $this->from . "\r\n";

        $license = Alchemyst_Forms_License::get_instance();

        if (Alchemyst_Forms_Settings::get_setting('use-smtp')) {
            $sent = Alchemyst_Forms_Elastic_Email::send($this->to, $this->from, $this->from_name, $this->subject, $loaded_template, $this->cc, $this->bcc, $this->attachments);
        }
        else {
            $sent = Alchemyst_Forms_Elastic_Email::send($this->to, $this->from, $this->from_name, $this->subject, $loaded_template, $this->cc, $this->bcc, $this->attachments, false);
        }

        if (!$sent)
            return $sent;
        else
            return $loaded_template;
    }

    public static function get_logo() {
        return get_template_directory_uri() . '/img/logo.png';
    }

    public static function get_templates() {
        $templates = array(
            'default.php' => 'Default Template',
            'plain.php' => 'Plain (Unstyled) Template'
        );

        return apply_filters('alchemyst_forms:email-templates', $templates);
    }
}
