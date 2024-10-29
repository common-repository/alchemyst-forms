<?php
/**
 * Registers the shortcode to display the form.
 *
 * Usage: [alchemyst-form id="{form_id}"]
 */
class Alchemyst_Forms_Shortcode {

    public static function init() {
        add_shortcode('alchemyst-form', array(__CLASS__, 'contact_form'));
    }

    public static function contact_form($atts) {
        $atts = shortcode_atts(array(
    		'id' => false,
    	), $atts, 'alchemyst-form');

        extract($atts);

        if (!$id || get_post_status($id) != 'publish')
            return "Invalid Contact Form ID Specified";

        global $form_id, $alchemyst_form;
        $form_id = $id;
        $alchemyst_form = Alchemyst_Form::prepare_for_output($form_id);

        return Alchemyst_Forms_Utils::load_frontend_template('alchemyst-form');
    }
}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Shortcode', 'init'));
