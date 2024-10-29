<?php
    /**
     * This is probably the most complicated file in the entire plugin. The entries list uses DataTables to list out
     * entries in a format that allows for columns to be hidden and re-arranged, and this layout is remembered by the
     * site so that users can customize how they wish the entries list to appear to them.
     *
     */
    $license = Alchemyst_Forms_License::get_instance();

    global $post;
    $form_id = $post->ID;
    $entries = Alchemyst_Forms_Entries::get_entries($form_id);
    $field_names = Alchemyst_Form::get_field_names_by_id($form_id);
    $entry_view_settings = Alchemyst_Form::get_entry_view_settings($form_id);

    $dom = Alchemyst_Form::get_dom($form_id);

    foreach ($field_names as $field_name) {
        $columns[Alchemyst_Forms_Utils::slugify($field_name)] = ucwords($field_name);


        if (!in_array($field_name, $entry_view_settings['field-order'])) {
            $entry_view_settings['field-order'][] = $field_name;
        }
    }

    $field_types = array();
    foreach ($entry_view_settings['field-order'] as $key => $field_name) {
        if (!in_array($field_name, $field_names)) {
            unset($entry_view_settings['field-order'][$key]);
        }

        $field_types[$key] = Alchemyst_Form::get_field_type($field_name, $dom);
    }

    $encrypted_fields = Alchemyst_Form::get_encrypted_fields($form_id, $dom);
?>

<p>
    <fieldset class="metabox-prefs alchemyst-forms-column-toggles">
		<legend>
            <strong>Show Columns</strong>
        </legend>
        <div class="alchemyst-forms-column-toggle-wrap">
            <?php $i = 1; foreach ($columns as $key => $column) :
                $checked = '';
                if (is_array($entry_view_settings['visible-fields']) && in_array($key, $entry_view_settings['visible-fields']))
                    $checked = ' checked="checked"';
                ?>
                <label for="alchemyst-forms-entry-column-<?=$key?>" data-alchemyst-forms-column-toggle-index="<?=$i?>" data-alchemyst-forms-column-toggle="<?=$key?>">
                    <input type="checkbox" id="alchemyst-forms-entry-column-<?=$key?>" data-alchemyst-forms-column-toggle="<?=$key?>" data-alchemyst-forms-column-toggle-index="<?=$i?>"<?=$checked?>>
                    <?=Alchemyst_Forms_Entries::interpret_key($column)?>
                </label>
            <?php $i++; endforeach; ?>
        </div>
	</fieldset>
</p>

<p>
    <strong><a data-af-slide-toggle="rm-field-description" href="#">Missing data from a removed field?</a></strong>
</p>
<p data-af-slide-toggle="rm-field-description">
    <em>Columns are determined based on what is currently available in your form. If you have removed a field that previous entries may have used and you would like to see this data again in the entries table, you can add a hidden field containing the old <code>name</code> attribute to your form. Otherwise you will be able to view the full entry details for this form at the time of submission by clicking on <strong>View Entry</strong> when hovering over that entries row.</em>
</p>
<p>
    <?php if ($license->license_is_valid()) : ?>
        <a href="<?=admin_url('edit.php?post_type=alchemyst-forms&page=alchemyst-forms-tools&af-export-form-id=' . $post->ID)?>#export-entries" class="button button-primary button-large">Export Entries From This Form</a>
    <?php endif; ?>

    <a href="<?=admin_url('edit.php?post_type=alchemyst-forms&page=alchemyst-forms-tools&af-bulk-delete-form-id=' . $post->ID)?>#bulk-delete-entries" class="button button-danger button-large">Bulk Delete Entries From This Form</a>
</p>

<div class="entry-notifications" style="display: none;">
    <p class="alchemyst-forms-valid-state">
        Entry ID [id] has been deleted.
        <a href="#" data-delete-entry-undo="[id]">Undo</a>
    </p>
</div>

<table class="alchemyst-forms-entries-table wp-list-table widefat fixed striped pages">
    <thead>
        <tr>
            <th data-alchemyst-forms-column-toggle="_af-entry-id">
                ID
            </th>
            <th data-alchemyst-forms-column-toggle="_af-entry-created-date">
                Entry Created Time
            </th>
            <?php foreach ($entry_view_settings['field-order'] as $field_name) : ?>
                <?php if ($field_name == "_af-entry-id" || $field_name == "_af-entry-created-date") continue; ?>
                <th data-alchemyst-forms-column-toggle="<?=$field_name?>">
                    <?=Alchemyst_Forms_Utils::unslugify($field_name)?>
                </th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($entries as $entry) : ?>
            <tr>
                <td data-sort="<?=$entry->ID?>">
                    <?=$entry->ID?>
                    <div class="row-actions">
                        <span class="edit">
                            <a href="<?=get_edit_post_link($entry->ID)?>">View Entry</a>
                            |
                        </span>
                        <span class="trash">
                            <a href="#" class="submitdelete" data-delete-entry="<?=$entry->ID?>">Delete Entry</a>
                        </span>
                    </div>
                </td>
                <td data-sort="<?=$entry->post->post_date?>">
                    <?=date('g:i:s a, ' . get_option('date_format'), strtotime($entry->post->post_date))?>
                </td>
                <?php foreach ($entry_view_settings['field-order'] as $key => $field_name) :

                        if ($field_name == "_af-entry-id" || $field_name == "_af-entry-created-date") continue;

                        $v = $entry->get_value($field_name);
                        $field_type = $field_types[$key];
                        $v = Alchemyst_Forms_Entries::parse_value($v, $field_type, $field_name, $form_id, $dom, $entry, in_array($field_name, $encrypted_fields), true);
                    ?>
                    <td data-field-name="<?=$field_name?>" data-field-type="<?=$field_type?>" data-encrypted="<?=(in_array($key, $encrypted_fields) ? 'true' : 'false')?>">
                        <?=$v?>
                    </td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div style="clear: both;"></div>
