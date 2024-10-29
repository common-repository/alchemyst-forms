<?php
use PHPHtmlParser\Dom;
/**
 * Entries helper class
 */

class Alchemyst_Forms_Entries {

    /**
     * Set up the wordpress actions used by this class.
     */
    public static function init() {
        add_action('edit_form_before_permalink', array(__CLASS__, 'show_back_link'));
    }

    /**
     * Show back link from entries view back to the form entries list.
     * @see admin-views/entries-back-link.php
     */
    public static function show_back_link() {
        global $post;

        $screen = get_current_screen();
        if ($post->post_type == AF_ENTRIES_POSTTYPE && $screen->action != 'add') {
            echo Alchemyst_Forms_Utils::load_admin_template('entries-back-link');
        }
    }

    /**
     * Get entries for a form based on ID. Mostly a wrapper around get_posts.
     *
     * @param $form_id - Alchemyst Form post ID to get entries for.
     * @return an array of Alchemst_Forms_Entry objects.
     */
    public static function get_entries($form_id) {
        $entries = array();

        $args = array(
            'post_type' => AF_ENTRIES_POSTTYPE,
            'posts_per_page' => -1,
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_alchemyst_forms-form-id',
                    'value' => $form_id
                )
            )
        );

        $posts = get_posts($args);

        foreach ($posts as $post) {
            $entries[] = new Alchemst_Forms_Entry($form_id, $post);
        }

        return $entries;
    }

    public static function get_entry_count($form_id) {
        return count(self::get_entries($form_id));
    }

    /**
     * Save an entry. Uses build_entry_arr to create the array.
     * There's some weird conditions going on with saving files that means in order to associate the request names
     * with the file uploads, we need to insert the post with a meta_input, then go back to it after the media files
     * have been attached to the post_id.
     *
     * @param $form_id - ID of the form that this entry is associated with
     * @param $request - $_REQUEST received by the plugin.
     * @param $files - Returned array of file results from Alchemyst_Forms_Submissions::handle_file_upload()
     * @param $dom - $dom received from Alchemyst_Form::get_dom();
     */
    public static function save_entry($form_id, $request, $files, $dom) {
        do_action('alchemyst_forms:save-entry', $form_id, $request, $files, $dom);

        $postarr = self::build_entry_arr($form_id, $request, $files, $dom);
        $post_id = wp_insert_post($postarr);

        foreach ($files as $file_array) {

            foreach ($file_array as $file) {
                // Set up the attachment array for inserting the attachment to the media library. Sets the parent post to this form.
                $attachment = array(
                    'post_title' => pathinfo($file['file'], PATHINFO_BASENAME),
                    'post_content' => '',
                    'post_status' => 'publish',
                    'post_mime_type' => $file['type']
                );
                $attachment_id = wp_insert_attachment($attachment, $file['file'], $post_id);

                // Generate attachment meta - sets up things like thumbnails.
                $attachment_meta = wp_generate_attachment_metadata($attachment_id, $file_result['file']);

                // Add it to the requests array, which annoyingly enough has already been saved, so we have to update it...
                $request = maybe_unserialize(get_post_meta($post_id, '_alchemyst_forms-request', true));
                $request[$file['field']][] = $attachment_id;
                update_post_meta($post_id, '_alchemyst_forms-request', $request);
            }
        }

        return $post_id;
    }

    /**
     * Build entry array for inserting a AF_ENTRIES_POSTTYPE post.
     *
     * @param $form_id - ID of the form that this entry is associated with
     * @param $request - $_REQUEST received by the plugin.
     * @param $files - Returned array of file results from Alchemyst_Forms_Submissions::handle_file_upload()
     * @param $dom - $dom received from Alchemyst_Form::get_dom();
     */
    public static function build_entry_arr($form_id, $request, $files, $dom) {
        $request = apply_filters('alchemyst_forms:entry-request', $request, $form_id, $dom);

        $field_names = Alchemyst_Form::get_field_names($dom, $form_id);

        foreach ($request as $key => $value) {
            $elem = $dom->find('[name="' . $key . '"]');
            if (count($elem)) {
                if ($elem[0]->getAttribute('data-encrypted') == "true") {
                    $request[$key] = Alchemyst_Forms_Utils::encrypt($value);
                }
            }
        }

        return array(
            'post_type' => AF_ENTRIES_POSTTYPE,
            'post_status' => 'publish',
            'post_title' => 'Entry for Form: ' . $form_id,
            'meta_input' => array(
                '_alchemyst_forms-form-id'      => $form_id,
                '_alchemyst_forms-request'      => $request,
                '_alchemyst-forms-field_names'  => $field_names
            )
        );
    }

    /**
     * Interprets $_REQUEST keys for display. Mostly handles formatting for reserved keys
     * Or hiding the ones that we don't want or need displayed on the back end.
     *
     * @param $key - $_REQUEST[$key] submitted with this form entry.
     */
    public static function interpret_key($key) {
        if ($key == '_alchemyst-forms-referrer') {
            return "Referring URL";
        }
        elseif ($key == "form_id" || $key == "_wpnonce" || $key == "action" || $key == "g-recaptcha-response") {
            return "";
        }
        else {
            return Alchemyst_Forms_Utils::unslugify($key);
        }
    }

    /**
     * Prepare a value to be displayed, either for a notification, or for view in the admin areas.
     * This function is a mess...
     *
     * @param $value - Value to adjust
     * @param $field_type - Type of field for this value.
     * @param $field_name - Name of the field we're parsing a value for (useful for extra dom lookups)
     * @param $form_id - Form ID of the form containing the field for this value
     * @param $dom - DOM object of the form.
     * @param $entry - The entry entry object
     * @param $encrypted - Is this value encrypted? Default false
     * @param $html_entities - Should we prepare this entry for display with HTML entities left in tact?
     * @param $format_urls - Not used.
     */
    public static function parse_value($value, $field_type, $field_name, $form_id, $dom, $entry, $encrypted = false, $html_entities = false, $format_urls = true) {
        // First, spaces get turned to underscores in the postdata when the form was submitted, so we need to convert

        // If field was encrypted, decrypt it
        if ($encrypted) {
            if ($value) {
                try {
                    $value = Alchemyst_Forms_Utils::decrypt($value);
                }
                catch (Exception $e) {
                    // Do nothing? Its possible that this field was made encrypted after it was initially set up.
                }
            }
        }

        if ($field_type == 'repeatable') {
            // We have some work to do to format the sub fields, and even get their values here...
            $repeater = $dom->find('repeatable[name="' . $field_name . '"]');
            if (!count($repeater)) {
                return false;
            }

            $minidom = new Dom;
            $minidom->load($repeater[0]->innerHtml());

            $field_names = Alchemyst_Form::get_field_names($minidom, $form_id);

            $sub_values = array();
            foreach ($field_names as $field_name) {
                $field_type = Alchemyst_Form::get_field_type($field_name, $minidom);
                $sub_values[$field_name] = $entry->get_value($field_name);
            }


            $return = '';

            $repeater_count = count(end($sub_values));

            // If there's only one field, we can just comma separate them.
            if ($repeater_count == 1 || $html_entities) {
                foreach ($sub_values as $field_name => $value) {
                    $field_type = Alchemyst_Form::get_field_type($field_name, $dom);

                    if (empty($value)) continue;
                    if (is_array($value)) $value = Alchemyst_Forms_Utils::array_to_string($value);

                    $value = self::parse_value($value, $field_type, $field_name, $form_id, $dom, $entry, $encrypted, $html_entities);
                    $return .= '<p><strong>' . Alchemyst_Forms_Utils::unslugify($field_name) . '</strong>: ' . $value . '</p>';
                }
            }
            else {
                // Else we'll format them into a table for easier viewing.
                for ($i = 0; $i < $repeater_count; $i++) {
                    $return .= '<h3>Row' . ($i+1) . '</h3>';
                    $return .= '<table class="form-table">';
                    foreach ($sub_values as $field_name => $value) {
                        $field_type = Alchemyst_Form::get_field_type($field_name, $dom);

                        $v = $value[$i];
                        if (is_array($v)) $v = Alchemyst_Forms_Utils::array_to_string($v);

                        $v = self::parse_value($v, $field_type, $field_name, $form_id, $dom, $entry, $encrypted, $html_entities);

                        $return .= '<tr>';
                            $return .= '<th>' . Alchemyst_Forms_Utils::unslugify($field_name) . '</th>';
                            $return .= '<td>' . $v . '</td>';
                        $return .= '</tr>';
                    }
                    $return .= '</table>';
                }
            }

            return $return;
        }

        if (is_array($value)) $value = Alchemyst_Forms_Utils::array_to_string($value);

        // For file uploads we're going to show a View Attachment link.
        if ($field_type == "file") {
            if (!empty($value)) {
                return '<a href="' . get_edit_post_link($value) . '">View Attachment</a>';
            }
        }

        // Sometimes we don't want to render HTML on the output.
        if ($html_entities) {
            $value = htmlentities($value);
        }

        // If its a URL, make it a link.
        if (filter_var($value, FILTER_VALIDATE_URL) !== false && $format_urls === true) {
            return '<a href="' . $value . '" target="_blank">' . $value . '</a>';
        }
        // If its an email address, make it a mailto: link
        elseif (filter_var($value, FILTER_VALIDATE_EMAIL) !== false && $format_urls === true) {
            return '<a href="mailto:' . $value . '" target="_blank">' . $value . '</a>';
        }
        else {
            // Determine output method. If we want to show the raw value, or its not a wysiwyg we have the same approach.
            // nl2br() is sufficient here

            if ($html_entities || $field_type != 'wysiwyg')
                return nl2br($value);
            else // we'll apply the content filters to appropriately format the wp_editor output
                return apply_filters('the_content', $value);
        }
    }
}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Entries', 'init'));

/**
 * Simple object for entry post types.
 */
class Alchemst_Forms_Entry {

    function __construct($form_id, $post) {
        $this->ID = $post->ID;
        $this->form_id = $form_id;
        $this->post = $post;
        $this->meta = Alchemyst_Forms_Utils::clean_meta(get_post_meta($post->ID));
    }

    public function get_value($field_name) {
        if ($this->meta['_alchemyst_forms-request'][$field_name])
            return $this->meta['_alchemyst_forms-request'][$field_name];
        else
            return false;
    }
}
