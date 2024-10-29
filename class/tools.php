<?php
/**
 * Tools section. Adds the menu pages and has class methods for the functionality.
 * Should be extendable by adding filters to alchemyst_forms:tools-sections
 */
class Alchemyst_Forms_Tools {
    public static function init() {
        self::add_menu_page();
    }

    public static function add_menu_page() {
        $menu_function = function() {
            $page_title = "Alchemyst Forms Tools";
            $menu_title = "Tools";
            $capability = "af-use-tools";
            $menu_slug = "alchemyst-forms-tools";
            $function = array(__CLASS__, 'callback');
            add_submenu_page('edit.php?post_type=' . AF_POSTTYPE, $page_title, $menu_title, $capability, $menu_slug, $function);
        };
        add_action('admin_menu', $menu_function);
    }

    public static function callback() {
        // Update options if $_POST.
        if ($_POST['af-export-form-id']) {
            $form_id = intval($_POST['af-export-form-id']);
            global $export_file_url;

            $export_file_url = self::do_csv_export($form_id);
        }

        if ($_POST['alchemyst-forms-delete-entries-form-id']) {
            $form_id = intval($_POST['alchemyst-forms-delete-entries-form-id']);
            self::do_bulk_entry_delete($form_id);
        }

        do_action('alchemyst_forms:tools-callback');

        // Load the view.
        echo Alchemyst_Forms_Utils::load_admin_template('tools');
    }

    public static function do_csv_export($form_id) {
        $entries = Alchemyst_Forms_Entries::get_entries($form_id);
        $field_names = Alchemyst_Form::get_field_names_by_id($form_id);
        $csv_title_row = array_merge(
            array('Entry ID'),
            array('Date'),
            array_map(array('Alchemyst_Forms_Utils', 'unslugify'), $field_names),
            array('Referring URL')
        );
        $entry_data = array();
        foreach ($entries as $entry) {
            $row = array();
            $row['ID'] = $entry->ID;
            $row['date'] = date('g:i:s a, ' . get_option('date_format'), strtotime($entry->post->post_date));

            foreach ($field_names as $field_name) {

                if (isset($entry->meta['_alchemyst_forms-request'][$field_name])) {
                    $v = $entry->meta['_alchemyst_forms-request'][$field_name];
                    if (is_array($v))
                        $v = Alchemyst_Forms_Utils::array_to_string($v);

                    $row[$field_name] = $v;
                } else {
                    $row[$field_name] = '';
                }
            }
            $row['referring-url'] = $entry->meta['_alchemyst_forms-request']['_alchemyst-forms-referrer'];
            $entry_data[] = $row;
        }

        $wp_upload_dir = wp_upload_dir();
        $dir = $wp_upload_dir['path'];
        $fname = 'alchemyst-forms-export-' . $form_id . '-' . time() . '.csv';

        $f = fopen($dir . '/' . $fname, 'w');

        $f_url = $wp_upload_dir['url'] . '/' . $fname;

        fputcsv($f, $csv_title_row);

        foreach($entry_data as $data) {
            fputcsv($f, $data);
        }
        fclose($f);

        return $f_url;
    }

    public static function do_bulk_entry_delete($form_id) {
        global $entries;
        $entries = Alchemyst_Forms_Entries::get_entries($form_id);
        $permadelete = $_POST['permanently-delete'] == "1";

        foreach ($entries as $entry) {
            wp_delete_post($entry->ID, $permadelete);
        }
    }
}
add_action('alchemyst_forms:loaded', array('Alchemyst_Forms_Tools', 'init'));
