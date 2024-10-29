<?php
use PHPHtmlParser\Dom;

/**
 * Helper functions for rendering and setting up the form for front end display.
 */
class Alchemyst_Form {

    function __construct($form_id, $html) {
        $this->ID = $form_id;
        $this->html = $html;
        $this->meta = Alchemyst_Forms_Utils::clean_meta(get_post_meta($this->ID));

        if (isset($this->meta['_alchemyst_forms-ga-submission-tracking'])) {
            $this->track_ga = $this->meta['_alchemyst_forms-ga-submission-tracking'] == 1 ? 'true' : 'false';
            if ($this->track_ga) {
                $this->track_ga_event_name = $this->meta['_alchemyst_forms-ga-submission-tracking-event-name'];
            }
        }
    }

    /**
     * A named method for getting a form, just a renamed prepare_for_output().
     */
    public static function get_form($form_id) {
        return self::prepare_for_output($form_id);
    }


    /**
     *
     */
    public static function get_all_forms() {
        $args = array(
            'post_type' => AF_POSTTYPE,
            'posts_per_page' => -1,
            'orderby' => 'post_title',
            'order' => 'ASC'
        );

        return get_posts($args);
    }

    /**
     * Render form validation for ajax forms
     */
    public static function render_form_validation() {
        ?>
        <div class="alert alert-danger alchemyst-forms-validation" role="alert" style="display: none;"></div>
        <div class="alert alert-success alchemyst-forms-success-validation" role="alert" style="display: none;"></div>
        <?php
    }

    /**
     * Render the form for display on the front end of the site.
     */
    public static function prepare_for_output($post_id, $do_replacements = true) {
        // We need to set up the ajax endpoint if it hasn't already been set up.
        $form_code = get_post_meta($post_id, '_alchemyst_forms_contact-form-code', true);

        $form_interpolator = new Alchemyst_Form_Interpolator($form_code);
        $html = $form_interpolator->interpolate();

        $html = self::modify_dom($html, $do_replacements, $post_id);

        add_action('alchemyst_forms:before-form-output', array(__CLASS__, 'render_validation'), 10, 2);
        add_action('alchemyst_forms:after-form-output', array(__CLASS__, 'hidden_inputs'), 10, 2);
        add_action('alchemyst_forms:after-form-output', array(__CLASS__, 'render_preloader'), 10, 2);

        return new Alchemyst_Form($post_id, $html);
    }

    public static function render_validation($form_id, $alchemyst_form) {
        if (apply_filters('alchemyst_forms:render-form-validation', true, $form_id, $alchemyst_form)) : ?>
            <?php Alchemyst_Form::render_form_validation(); ?>
        <?php endif;
    }
    public static function hidden_inputs($form_id, $alchemyst_form) {
        if (isset($_SERVER['HTTP_REFERER'])) : ?>
            <input type="hidden" name="_alchemyst-forms-referrer" value="<?=$_SERVER['HTTP_REFERER']?>">
        <?php endif;
    }
    public static function render_preloader($form_id, $alchemyst_form) {
        if (apply_filters('alchemyst_forms:render-preloader', true, $form_id, $alchemyst_form)) : ?>
            <div class="alchemyst-forms-preloader"></div>
        <?php endif;
    }

    /**
     * Dom modifications for front end display.
     */
    public static function modify_dom($html, $do_replacements = true, $form_id) {
        $license = Alchemyst_Forms_License::get_instance();

        // TODO: Find root of this bug, this is a temporary fix.
        /*
            The bug results in curly braces wrapped around some arbirtrary amount of text getting removed from the DOM
            This means users that wish to do things like template replacements with jQuery on strings like '{sometext}'
            will run into some really odd and inconsistent issues. Simply replacing the curly braces here will fix the
            dom issue, but is not the root of the bug. Root of the bug might be in the html parser...
         */
        $html = str_replace('{', '\\LCURL\\', $html);
        $html = str_replace('}', '\\RCURL\\', $html);

        $dom = new Dom;
        $dom->load($html);


        // Any direct dom modifications should be completed by this point.
        $html = $dom->root->innerHtml();

        // TODO: Find root of this bug, this is a temporary fix.
        $html = apply_filters('alchemyst-forms:modify-dom', $html, $form_id, $dom);

        // TODO: Find root of this bug, this is a temporary fix.
        $html = str_replace('\\LCURL\\', '{', $html);
        $html = str_replace('\\RCURL\\', '}', $html);

        $html .= '<input type="hidden" name="form_id" value="' . $form_id . '">';
        $html .= '<input type="hidden" name="_wpnonce" value="' . Alchemyst_Forms_Utils::create_nonce('_alchemyst_forms_nonce') . '">';
        $html .= '<input type="hidden" name="action" value="alchemyst-form-submit">';

        return $html;
    }

    public static function replace_repeatable_fields(&$dom) {
        $repeatable_fields = $dom->find('repeatable');
        foreach ($repeatable_fields as $repeatable) {

            $node_id = $repeatable->id();

            $required_count = $repeatable->getAttribute('data-required-count');
            $maximum_count = $repeatable->getAttribute('data-maximum-count');
            $add_label = $repeatable->getAttribute('data-add-label');
            $minus_label = $repeatable->getAttribute('data-minus-label');

            $tag = $repeatable->getTag();
            $content = $repeatable->innerHtml();

            $new_html = <<<HTML
            <div class="alchemyst-forms-repeater-field" data-num-fields="1">
                <div class="repeat-fields">
                    <div class="repeat-wrap">
                        {$content}
                    </div>
                </div>
            </div>
HTML;

            $replace_minidom = new Dom;
            $replace_minidom->load($new_html);

            $repeater_arr = $replace_minidom->find('.alchemyst-forms-repeater-field');
            $repeater = $repeater_arr[0];

            if ($required_count)
                $repeater->setAttribute('data-required-count', $required_count);
            if ($maximum_count)
                $repeater->setAttribute('data-maximum-count', $maximum_count);
            if ($add_label)
                $repeater->setAttribute('data-add-label', $add_label);
            if ($minus_label)
                $repeater->setAttribute('data-minus-label', $minus_label);


            $repeater_fields = $replace_minidom->find('input, textarea, select');
            foreach ($repeater_fields as $field) {
                $name = $field->getAttribute('name');
                if (!$name) continue;

                $arr_pattern = "/\[\]/";

                if (!preg_match($arr_pattern, $name))
                    $field->setAttribute('name', $name . '[]');
            }

            $repeatable->getParent()->replaceChild($node_id, $replace_minidom->root);
        }
    }

    public static function replace_wysiwygs($html, $dom) {
        $settings = array( 'media_buttons' => false, 'teeny' => true, 'quicktags' => false );
        $settings = apply_filters('alchemyst_forms:wysiwyg-editor-settings', $settings);

        $wysiwygs = $dom->find('input[type="wysiwyg"]');
        foreach ($wysiwygs as $wysiwyg) {
            if (!$wysiwyg->name) continue;
            $tag = $wysiwyg;
            $value = $wysiwyg->val ? $wysiwyg->val : '';
            $id = $wysiwyg->name;

            ob_start(); // Why wont wp editor just let us return the HTML...
            wp_editor($value, $id, $settings);
            $editor = ob_get_clean();

            $html = str_replace($tag, $editor, $html);
        }

        return $html;
    }

    /**
     * Gather required fields. Return an array of the field names (inputs, selects, textareas)
     *
     * @param $post_id - Contact form's post id to get required fields for.
     * @param $dom - $dom object provided from Alchemyst_Form::get_dom() or equivelant.
     */
    public static function get_required_fields($post_id, $dom) {
        $fields = array();

        $inputs = $dom->find('input');
        foreach ($inputs as $input) {
            if ($input->getAttribute('data-required') == "true")
                $fields[] = $input->getAttribute('name');
        }

        $inputs = $dom->find('select');
        foreach ($inputs as $input) {
            if ($input->getAttribute('data-required') == "true")
                $fields[] = $input->name;
        }

        $inputs = $dom->find('textarea');
        foreach ($inputs as $input) {
            if ($input->getAttribute('data-required') == "true")
                $fields[] = $input->name;
        }

        return $fields;
    }

    /**
     * Gather required fields. Return an array of the field names (inputs, selects, textareas)
     *
     * @param $post_id - Contact form's post id to get required fields for.
     * @param $dom - $dom object provided from Alchemyst_Form::get_dom() or equivelant.
     */
    public static function get_encrypted_fields($post_id, $dom) {
        $fields = array();

        $inputs = $dom->find('input');
        foreach ($inputs as $input) {
            if ($input->getAttribute('data-encrypted') == "true")
                $fields[] = $input->name;
        }

        $inputs = $dom->find('select');
        foreach ($inputs as $input) {
            if ($input->getAttribute('data-encrypted') == "true")
                $fields[] = $input->name;
        }

        $inputs = $dom->find('textarea');
        foreach ($inputs as $input) {
            if ($input->getAttribute('data-encrypted') == "true")
                $fields[] = $input->name;
        }

        return $fields;
    }

    /**
     * Return an array of matching fields (key matches value for field names)
     *
     * @param $post_id - Contact form's post id to get required fields for.
     * @param $dom - $dom object provided from Alchemyst_Form::get_dom() or equivelant.
     */
    public static function get_matching_fields($post_id, $dom) {
        $fields = array();

        $inputs = $dom->find('input');
        foreach ($inputs as $input) {
            $data_matches = $input->getAttribute('data-matches');
            if (!empty($data_matches))
                $fields[$input->name] = $input->getAttribute('data-matches');
        }

        $inputs = $dom->find('select');
        foreach ($inputs as $input) {
            $data_matches = $input->getAttribute('data-matches');
            if (!empty($data_matches))
                $fields[$input->name] = $input->getAttribute('data-matches');
        }

        $inputs = $dom->find('textarea');
        foreach ($inputs as $input) {
            $data_matches = $input->getAttribute('data-matches');
            if (!empty($data_matches))
                $fields[$input->name] = $input->getAttribute('data-matches');
        }

        return $fields;
    }

    /**
     * Returns an array of minimum length fields (key = field name, value = (int) min-length)
     *
     * @param $post_id - Contact form's post id to get required fields for.
     * @param $dom - $dom object provided from Alchemyst_Form::get_dom() or equivelant.
     */
    public static function get_minimum_lengths($post_id, $dom) {
        $fields = array();

        $inputs = $dom->find('input');
        foreach ($inputs as $input) {
            $data_min_length = $input->getAttribute('data-min-length');
            if (!empty($data_min_length))
                $fields[$input->name] = (int) $input->getAttribute('data-min-length');
        }

        $inputs = $dom->find('select');
        foreach ($inputs as $input) {
            $data_min_length = $input->getAttribute('data-min-length');
            if (!empty($data_min_length))
                $fields[$input->name] = (int) $input->getAttribute('data-min-length');
        }

        $inputs = $dom->find('textarea');
        foreach ($inputs as $input) {
            $data_min_length = $input->getAttribute('data-min-length');
            if (!empty($data_min_length))
                $fields[$input->name] = (int) $input->getAttribute('data-min-length');
        }

        return $fields;
    }

    /**
     * Returns an array of maximum length fields (key = field name, value = (int) max-length)
     *
     * @param $post_id - Contact form's post id to get required fields for.
     * @param $dom - $dom object provided from Alchemyst_Form::get_dom() or equivelant.
     */
    public static function get_maximum_lengths($post_id, $dom) {
        $fields = array();

        $inputs = $dom->find('input');
        foreach ($inputs as $input) {
            $data_max_length = $input->getAttribute('data-max-length');
            if (!empty($data_max_length))
                $fields[$input->name] = (int) $input->getAttribute('data-max-length');
        }

        $inputs = $dom->find('select');
        foreach ($inputs as $input) {
            $data_max_length = $input->getAttribute('data-max-length');
            if (!empty($data_max_length))
                $fields[$input->name] = (int) $input->getAttribute('data-max-length');
        }

        $inputs = $dom->find('textarea');
        foreach ($inputs as $input) {
            $data_max_length = $input->getAttribute('data-max-length');
            if (!empty($data_max_length))
                $fields[$input->name] = (int) $input->getAttribute('data-max-length');
        }

        return $fields;
    }

    /**
     * Get a $dom object for a given form's post ID
     *
     * @param $form_id - Contact form's ID to get the $dom for.
     */
    public static function get_dom($form_id) {
        $form = self::prepare_for_output($form_id, false);
        $dom = new Dom;
        $dom->load($form->html);
        return apply_filters('alchemyst_forms:get-dom', $dom, $form_id, $form);
    }

    /**
     * Parse the dom for form values.
     */
    public static function get_field_names($dom, $form_id) {
        $names = $dom->find('[name]');
        $field_names = array();
        foreach ($names as $name) {
            $n = $name->getAttribute('name');

            if (strpos($n, '[') !== false) {
                $n = substr($n, 0, strpos($n, '['));
            }

            // PHP replaces spaces with underscores, so we need to undo that.
            $n = str_replace(' ', '_', $n);

            $field_names[] = $n;
        }
        return apply_filters('alchemyst_forms:get-field-names', array_unique($field_names), $form_id, $dom);
    }

    /**
     * If you want to get a form's field names by the form ID if you dont' already have a $dom available, use this.
     *
     * @param $form_id - Form's post ID to get field names for.
     */
    public static function get_field_names_by_id($form_id) {
        return self::get_field_names(self::get_dom($form_id), $form_id);
    }

    /**
     * Entry view settings
     *
     * Get the entry view settings based on the current logged in user ID. This should never be called where nopriv
     * is possible since it will fail.
     *
     * @param $form_id - Form post ID to get the view settinsg for.
     */
    public static function get_entry_view_settings($form_id) {
        $entry_settings = get_post_meta($form_id, '_alchemyst_forms-entry-view-settings-' . get_current_user_id(), true);

        if ($entry_settings) {
            return maybe_unserialize($entry_settings);
        }
        else {
            $field_names = self::get_field_names_by_id($form_id);

            $i = 0;
            $visible_fields = array();
            $field_order = array();
            foreach ($field_names as $field_name) {
                $field_order[] = $field_name;
                if ($i < 8) {
                    $visible_fields[] = $field_name;
                }
                $i++;
            }

            return array(
                'visible-fields' => $visible_fields,
                'field-order' => $field_order
            );
        }
    }

    /**
     * Simple post meta update to save entry view settings
     *
     * @param $form_id - Form's post ID to save the post meta for.
     * @param $user_id - Current user's ID, but can be specified anyway.
     * @param $visible_fields - Currently selected visible fields.
     * @param $field_order - Ordered array of fields in the LTR order they should be displayed as.
     */
    public static function save_entry_view_settings($form_id, $user_id, $visible_fields, $field_order) {
        update_post_meta($form_id, '_alchemyst_forms-entry-view-settings-' . $user_id, array(
            'visible-fields' => $visible_fields,
            'field-order' => $field_order
        ));
    }

    /**
     * Given a field name of an input[type="file"], return an array of settings that this upload must conform to.
     * We cannot rely on client side stuff for this.
     *
     * References the default settings that are supplied in Alchemyst_Forms_Settings
     *
     * @param $field_name - Name of the input[type="file"] to get restrictions for.
     * @param $dom - $dom provided from Alchemyst_Form::get_dom()
     */
    public static function get_file_upload_restrictions($field_name, $dom) {
        $file = $dom->find('input[name="' . $field_name . '"]');

        if (!count($file)) return false;

        $check_attributes = array(
            'data-max-file-size' => $file->getAttribute('data-max-file-size'),
            'data-allowed-types' => $file->getAttribute('data-allowed-types'),
            'data-max-width' => $file->getAttribute('data-max-width'),
            'data-max-height' => $file->getAttribute('data-max-height')
        );

        $max_size = $check_attributes['data-max-file-size'] ? $check_attributes['data-max-file-size'] : Alchemyst_Forms_Settings::get_setting('upload-max-file-size');

        $allowed_types = array_map('trim', explode(',', ($check_attributes['data-allowed-types'] ? $check_attributes['data-allowed-types'] : Alchemyst_Forms_Settings::get_setting('upload-allowable-file-types'))));

        $max_width = $check_attributes['data-max-width'] ? $check_attributes['data-max-width'] : Alchemyst_Forms_Settings::get_setting('upload-max-image-width');

        $max_height = $check_attributes['data-max-height'] ? $check_attributes['data-max-height'] : Alchemyst_Forms_Settings::get_setting('upload-max-image-height');

        if (empty($allowed_types) || !is_array($allowed_types)) {
            return false;
        }
        elseif (!$max_size) {
            return false;
        }

        if (in_array('jpg', $allowed_types) && !in_array('jpeg', $allowed_types)) $allowed_types[] = 'jpeg';
        if (in_array('jpeg', $allowed_types) && !in_array('jpg', $allowed_types)) $allowed_types[] = 'jpg';

        $image_types = array('jpg', 'jpeg', 'png', 'gif');
        $is_image_only = true;

        foreach ($allowed_types as $type) {
            if (!in_array($type, $image_types)) $is_image_only = false;
        }

        return array(
            'max-file-size' => $max_size,
            'allowable-file-types' => $allowed_types,
            'max-width' => $is_image_only ? $max_width : false,
            'max-height' => $is_image_only ? $max_height : false
        );
    }

    /**
     * Get the field type for a given field_name.
     * Useful for displaying entry rows differently depending on the type of field being used.
     *
     * Treats textareas, and selects as "text" inputs.
     *
     * @param $field_name - Name of the field to get the field type for.
     * @param $dom - $dom provided from Alchemyst_Form::get_dom()
     */
    public static function get_field_type($field_name, $dom) {
        $input = $dom->find('[name="' . $field_name . '"]');
        if (!count($input)) {
            $input = $dom->find('[name="' . $field_name . '[]"]');
            if (!count($input)) {
                return false;
            }
        }

        $tag_name = $input->getTag()->name();

        $return_type = '';
        if ($input->type) {
            $return_type = $input->type;
        }
        else {
            if ($tag_name == 'repeatable')
                $return_type = 'repeatable';
            else
                $return_type = 'text';
        }

        return $return_type;
    }
}
