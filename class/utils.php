<?php
/**
 * Some generic utilities.
 */
class Alchemyst_Forms_Utils {

    /**
     * Standardizes a method to write to a log file.
     * Convenient for debugging purposes where outputting information is not practical.
     * @param $text - String containing text to write to the log file.
     * @param $flag - Denotes the severity of this log entry. Default is "INFO". Use words such as "ERROR" or "WARNING" here.
     */
    public static function write_to_log($text, $flag = "INFO") {
        $d = '[' . date('r') . '] ' . '(' . $flag . ') ';
        return @file_put_contents(__DIR__ . '/../log.txt', $d . $text . "\n", FILE_APPEND);
    }

    /**
     * Uses output buffering to include and return a template as text.
     * Useful for keeping the more controller-like classes clean.
     *
     * Templates are first looked for in the current active theme or child theme, in the 'alchemyst-forms/' directory.
     */
    public static function get_include_template($file) {
        $basename = pathinfo($file, PATHINFO_BASENAME);
        ob_start();
        if ($overridden_template = locate_template('alchemyst-forms/' . $basename)) {
            // locate_template() returns path to file
            // if either the child theme or the parent theme have overridden the template
            include($overridden_template);
        } elseif ($overridden_template = locate_template('alchemyst-forms/email/' . $basename)) {
            include($overridden_template);
        } else {
            // If neither the child nor parent theme have overridden the template,
            // we load the template from the 'templates' sub-directory of the directory this file is in
            include($file);
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    /**
     * Shorter form of using get_include_template, for admin templates.
     * Also allows the option to add the .php automatically or not.
     *
     * @param $template - Name of this template to include.
     * @param $add_extension - Automatically add .php. Defaults to true.
     */
    public static function load_admin_template($template, $add_extension = true) {
        if ($add_extension)
            return self::get_include_template(ALCHEMYST_FORMS__PLUGIN_DIR . '/admin-views/' . $template . '.php');
        else
            return self::get_include_template(ALCHEMYST_FORMS__PLUGIN_DIR . '/admin-views/' . $template);
    }

    /**
     * Front end template inclusion. Same as above, just aa different folder.
     * Also allows the option to add the .php automatically or not.
     *
     * @param $template - Name of this template to include.
     * @param $add_extension - Automatically add .php. Defaults to true.
     */
    public static function load_frontend_template($template, $add_extension = true) {
        if ($add_extension)
            return self::get_include_template(ALCHEMYST_FORMS__PLUGIN_DIR . '/templates/' . $template . '.php');
        else
            return self::get_include_template(ALCHEMYST_FORMS__PLUGIN_DIR . '/templates/' . $template);
    }

    /**
     * Easier way to regiser post types witout having to worry so much about labels. Any args can be overwritten with $sender_args
     * Register Post Type Demo
     *
     * @param $post_type_name - Name of the post type to register - should be slug-like
     * @param $singular_name - Singular name - capitalization does not matter.
     * @param $plural_name - Plural name, capitalization does not matter.
     * @param $sender_args - Override any of the default args that this function assumes, or add additional ones.
     */
    public static function register_post_type($post_type_name, $singular_name, $plural_name, $sender_args = array()) {
        $lambda = function() use (&$post_type_name, &$singular_name, &$plural_name, &$sender_args) {
            $labels = array(
                'name'               => _x(ucwords($plural_name), 'post type general name', ALCHEMYST_FORMS_TEXTDOMAIN),
                'singular_name'      => _x(ucwords($singular_name), 'post type singular name', ALCHEMYST_FORMS_TEXTDOMAIN),
                'menu_name'          => _x(ucwords($plural_name), 'admin menu', ALCHEMYST_FORMS_TEXTDOMAIN),
                'name_admin_bar'     => _x(ucwords($singular_name), 'add new on admin bar', ALCHEMYST_FORMS_TEXTDOMAIN),
                'add_new'            => _x('Add New', strtolower($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'add_new_item'       => __('Add New ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'new_item'           => __('New ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'edit_item'          => __('Edit ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'view_item'          => __('View ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'all_items'          => __('All ' . ucwords($plural_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'search_items'       => __('Search ' . ucwords($plural_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'parent_item_colon'  => __('Parent ' . ucwords($plural_name) . ':', ALCHEMYST_FORMS_TEXTDOMAIN),
                'not_found'          => __('No ' . strtolower($plural_name) . ' found.', ALCHEMYST_FORMS_TEXTDOMAIN),
                'not_found_in_trash' => __('No ' . strtolower($plural_name) . ' found in Trash.', ALCHEMYST_FORMS_TEXTDOMAIN)
            );

            $defaults = array(
                'labels' => $labels,
                '_builtin' => false,
                'public' => true,
                'show_ui' => true,
                'show_in_nav_menus' => true,
                'hierarchical' => false,
                'capability_type' => 'post',
                'rewrite' => array(
                    'slug' => strtolower($post_type_name),
                    'with_front' => true,
                ),
                'supports' => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'custom-fields',
                    'comments'
                )
            );


            $args = array_merge($defaults, $sender_args);

            register_post_type($post_type_name, $args);
        };
        add_action('init', $lambda);
    }

    /**
     * Easier way to regiser taxonomies witout having to worry so much about labels. Any args can be overwritten with $sender_args
     * $supported_post_types can be a string or an array
     *
     * @param $taxonomy_name - Name of this taxonomy. Should be slug-like.
     * @param $supported_post_types - Single string, or array of supported post types.
     * @param $singular_name - Singular name - capitalization does not matter.
     * @param $plural_name - Plural name, capitalization does not matter.
     * @param $sender_args - Override any of the default args that this function assumes, or add additional ones.
     */
    public static function register_taxonomy($taxonomy_name, $supported_post_types, $singular_name, $plural_name, $sender_args = array()) {

        $lambda = function() use (&$taxonomy_name, &$supported_post_types, &$singular_name, &$plural_name, &$sender_args) {
            $labels = array(
                'name'              => _x(ucwords($plural_name), 'taxonomy general name', ALCHEMYST_FORMS_TEXTDOMAIN),
                'singular_name'     => _x(ucwords($singular_name), 'taxonomy singular name', ALCHEMYST_FORMS_TEXTDOMAIN),
                'search_items'      => __('Search ' . ucwords($plural_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'all_items'         => __('All ' . ucwords($plural_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'parent_item'       => __('Parent ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'parent_item_colon' => __('Parent ' . ucwords($singular_name) . ':', ALCHEMYST_FORMS_TEXTDOMAIN),
                'edit_item'         => __('Edit ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'update_item'       => __('Update ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'add_new_item'      => __('Add New ' . ucwords($singular_name), ALCHEMYST_FORMS_TEXTDOMAIN),
                'new_item_name'     => __('New ' . ucwords($singular_name) . ' Name', ALCHEMYST_FORMS_TEXTDOMAIN),
                'menu_name'         => __(ucwords($plural_name), ALCHEMYST_FORMS_TEXTDOMAIN),
            );

            $defaults = array(
                'hierarchical'      => true,
                'labels'            => $labels,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => strtolower($singular_name)),
            );

            $args = array_merge($defaults, $sender_args);

            register_taxonomy($taxonomy_name, $supported_post_types, $args);
        };

        add_action('init', $lambda);
    }

    /**
     * Nonce Helpers
     * These are necessary because the Wordpress implementation of nonce is totally not what a nonce actually should be.
     * Nonce should be one time use only, and always.
     *
     * NOTE: These do not work properly yet (but they do work equally well to how the wp nonces work)
     * TODO: Write custom implementation of nonce?
     *
     * @param $nonce_name - Name of this Nonce. Will be additionally sanitized with a microtime(true) call.
     */
    public static function create_nonce($nonce_name) {
        $mt = microtime(true);
        $nonce_val = wp_create_nonce($nonce_name . $mt);
        $nonce_str = $mt . '-' . $nonce_val;
        return $nonce_str;
    }

    /**
     * Verify a nonce built with Alchemyst_Forms_Utils::create_nonce
     *
     * @param $nonce - Nonce to verify;
     * @param $nonce_name - Name of the nonce to verify - sans the microtime of course.
     */
    public static function verify_nonce($nonce, $nonce_name) {
        $nonce_parts = explode('-', $nonce);
        // parts[0] is the creation microtime, parts[1] is the nonce_val to verify
        if (!is_array($nonce_parts)) return 0;
        if (count($nonce_parts) != 2) return -1;
        return wp_verify_nonce($nonce_parts[1], $nonce_name . $nonce_parts[0]);
    }

    /**
     * Takes a nasty looking array and makes it look prettier.
     * Do not use on get_post_meta where keys are not always unique.
     *
     * @param $meta - Expects the results from get_post_meta($post_id) (with no additional parameters)
     */
    public static function clean_meta($meta) {
        $meta_holder = array();
        if (!is_array($meta)) {
            return false;
        }
        foreach ($meta as $key => $val) {
            $meta_holder[$key] = maybe_unserialize($val[0]);
        }
        return $meta_holder;
    }

    /**
     * Simple array to string conversion.
     * Supports multidimensional arrays (implode() does not)
     *
     * @param $array - Array to convert to string.
     * @param $glue - Glue to piece the array together with. Will be trimmed from the end.
     */
    public static function array_to_string($array, $glue = ', ') {
        $ret = '';

        foreach ($array as $item) {
            if (is_array($item)) {
                $ret .= self::array_to_string($item, $glue) . $glue;
            } else {
                $ret .= $item . $glue;
            }
        }

        $ret = substr($ret, 0, 0-strlen($glue));

        return $ret;
    }

    /**
     * This used to be a custom implementation. Now it just wraps around Wordpress' sanitize_title function.
     * Maintained for compatibility, and slugify makes more sense as a function name to me.
     *
     * @param $text - text to slugify.
     */
    public static function slugify($text) {
        return sanitize_title($text);
    }

    /**
     * Simple format helper
     * Will turn a string like 'this-is-a-field-name' to 'This Is A Field Name'
     *
     * @param $string - String to unslugify. Not guaranteed to be a match of how the string was before being slugified.
     */
    public static function unslugify($string) {
        return ucwords(str_replace("_", " ", str_replace('-', ' ', $string)));
    }

    /**
     * Turn an integer into a more readable format (bytes to KB, MB, GB)
     * Modified from @source http://stackoverflow.com/a/2510540
     *
     * @param $size - Number of bytes.
     * @param $precision - Number of decimal places to include in the round().
     */
    public static function format_bytes($size, $precision = 2) {
         $base = log($size, 1024);
         $suffixes = array('', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB');

         return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
    }

    // Holy function name, Batman!
    public static function get_interpolation_options_as_string_from_message($message) {
        $pattern = '/\[(\w+)\]/';
        preg_match_all($pattern, $message, $matches);
        return self::array_to_string(array_unique($matches[0]));
    }

    public static function get_encryption_key() {
        $wud = wp_upload_dir();
        $location =  $wud['basedir'] . '/alchemyst-contact-forms/encryption-key.txt';
        if (!file_exists($location)) {
            self::write_encryption_key();
        }

        $key = file_get_contents($location);
        return $key;
    }

    public static function write_encryption_key() {
        $wud = wp_upload_dir();
        $location =  $wud['basedir'] . '/alchemyst-contact-forms/encryption-key.txt';
        if (file_exists($location)) return;

        if (!is_dir(dirname($location))) {
            mkdir(dirname($location), 0755, true);
        }

        $f = fopen($location, 'w');
        if ($f === false) {
            self::_exit('We had some troubles writing an encryption key. Your server may be misconfigured');
        }
        fwrite($f, base64_encode(openssl_random_pseudo_bytes(4096)));
        fclose($f);

        // Write an htaccess to deny access to this file.
        $location2 = $wud['basedir'] . '/alchemyst-contact-forms/.htaccess';
        $f2 = fopen($location2, 'w');
        if ($f2 === false) {
            self::_exit('We had some troubles writing an encryption key. Your server may be misconfigured');
        }
        fwrite($f2, "<Files \"encryption-key.txt\">
Order Allow,Deny
Deny from all
</Files>");
        fclose($f2);
    }

    /**
     * NOTE: Code removed from free version.
     */
    public static function encrypt($string) {
        return $string;
    }

    /**
     * NOTE: Code removed from free version.
     */
    public static function decrypt($string) {
        return $string;
    }

    /**
     * Renders out the Google Analytics  javascript. Added to a template action.
     */
    public static function render_ga_tracking($alchemyst_form) {
        $license = Alchemyst_Forms_License::get_instance();
        if (isset($alchemyst_form->track_ga) && $alchemyst_form->track_ga == 'true' && $license->license_is_valid()) : ?>
        <script>
        jQuery(document).ready(function($) {
            $('[data-alchemyst-form-id="<?=$alchemyst_form->ID?>"]').on('alchemyst_forms:submission_success', function(response, $el) {
                var track_ga = <?=$alchemyst_form->track_ga?>;
                if (track_ga) {
                    if (window.ga) {
                        window.ga('send', {
                            hitType: 'event',
                            eventCategory: 'Form',
                            eventAction: 'submit',
                            eventLabel: '<?=$alchemyst_form->track_ga_event_name?>',
                            eventValue: <?=$alchemyst_form->ID?>
                        });
                    }
                }
            });
        });
        </script>
        <?php endif;
    }

    public static function render_logo() {
        ?>
        <div class="alchemyst-forms-logo">
            <a href="https://alchemyst.io" target="_blank"><img src="<?=ALCHEMYST_FORMS__PLUGIN_URL?>css/logo.png" alt="Alchemyst" title="Powered By Alchemyst"></a>
        </div>
        <?php
    }

    public static function render_postlist_logo() {
        $screen = get_current_screen();

        if ($screen->base == 'edit' &&
            ($screen->parent_file == 'edit.php?post_type=' . AF_POSTTYPE ||
            $screen->parent_file == 'edit.php?post_type=' . AF_NOTIFICATIONS_POSTTYPE ||
            $screen->parent_file == 'edit.php?post_type=' . AF_ENTRIES_POSTTYPE)
        ) :
            ?><div class="alchemyst-forms-logo">
                <a href="https://alchemyst.io" target="_blank"><img src="<?=ALCHEMYST_FORMS__PLUGIN_URL?>css/logo.png" alt="Alchemyst" title="Powered By Alchemyst"></a>
            </div><?php
        endif;
    }

    public static function _exit($message, $title = "Alchemyst Forms Error") {
        wp_die($message);
        die();
    }
}
