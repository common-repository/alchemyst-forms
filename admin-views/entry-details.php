<?php
    global $entry;

    $form_id = $entry->meta['_alchemyst_forms-form-id'];
    $dom = Alchemyst_Form::get_dom($form_id);
    $all_fields = Alchemyst_Form::get_field_names($dom, $form_id);

    $fields_as_keys = array();
    $field_types = array();
    foreach ($all_fields as $field_name) {
        $fields_as_keys[$field_name] = '';
        $field_types[$field_name] = Alchemyst_Form::get_field_type($field_name, $dom);
    }

    $entry_fields = array_merge($fields_as_keys, $entry->meta['_alchemyst_forms-request']);

    $encrypted_fields = Alchemyst_Form::get_encrypted_fields($form_id, $dom);

?>
<table class="form-table entry-details-table">
    <tr>
        <th>
            Entry ID
        </th>
        <td>
            <?=$entry->ID?>
        </td>
    </tr>
    <tr>
        <th>
            Entry Time
        </th>
        <td>
            <?php the_time(); ?>, <?=get_the_date();?>
        </td>
    </tr>
    <?php foreach($entry_fields as $field_name => $value) :
        $ov = $value;
        $value = Alchemyst_Forms_Entries::parse_value($value, $field_types[$field_name], $field_name, $form_id, $dom, $entry, in_array($field_name, $encrypted_fields));
        $field_name = Alchemyst_Forms_Entries::interpret_key($field_name);

        if (!empty($field_name)) : ?>
            <tr>
                <th>
                    <?=$field_name?>
                </th>
                <td class="alchemyst-forms-value">
                    <?=$value?>
                </td>
            </tr>
        <?php endif;
    endforeach; ?>
</table>
