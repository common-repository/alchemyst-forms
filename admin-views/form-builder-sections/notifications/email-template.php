<table class="form-table">
    <tr>
        <th>
            To<br>
            <small>Separate multiple addresses with commas.</small>
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][to]" value="<?=htmlentities($notification->to)?>">
        </td>
    </tr>
    <tr>
        <th>
            CC<br>
            <small>Separate multiple addresses with commas.</small>
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][cc]" value="<?=htmlentities($notification->cc)?>">
        </td>
    </tr>
    <tr>
        <th>
            BCC<br>
            <small>Separate multiple addresses with commas.</small>
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][bcc]" value="<?=htmlentities($notification->bcc)?>">
        </td>
    </tr>
    <tr>
        <th>
            From
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][from_name]" value="<?=htmlentities($notification->from_name)?>">
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][from]" value="<?=htmlentities($notification->from)?>">
        </td>
    </tr>
    <tr>
        <th>
            Subject
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][subject]" value="<?=htmlentities($notification->subject)?>">
        </td>
    </tr>
    <tr>
        <th>
            Message Body<br>
            <small>HTML is allowed.</small>
        </th>
        <td>
            <div class="alchemyst-forms-editor-wrap">
                <pre id="form-notification-content-editor-<?=$notification->ID?>"><?=htmlentities($notification->email)?></pre>
            </div>
            <input type="hidden" name="alchemyst-forms-notification[<?=$notification->ID?>][email]" value="">
            <script>
                if (typeof neditor === 'undefined') {
                    var neditor = {};
                }
                var editor_theme = (alchemyst_forms_admin_js.ace_theme ? alchemyst_forms_admin_js.ace_theme : 'chrome');
                var margin_column = parseInt(alchemyst_forms_admin_js.editor_preferred_line_length) ? parseInt(alchemyst_forms_admin_js.editor_preferred_line_length) : 999999;
                var font_size = parseInt(alchemyst_forms_admin_js.editor_font_size) ? parseInt(alchemyst_forms_admin_js.editor_font_size) : 12;
                neditor['<?=$notification->ID?>'] = ace.edit('form-notification-content-editor-<?=$notification->ID?>');
                neditor['<?=$notification->ID?>'].setTheme("ace/theme/" + editor_theme);
                neditor['<?=$notification->ID?>'].session.setMode("ace/mode/html");
                neditor['<?=$notification->ID?>'].setPrintMarginColumn(margin_column);
                neditor['<?=$notification->ID?>'].setFontSize(font_size);

                jQuery(document).ready(function($) {
                    neditor['<?=$notification->ID?>'].resize();
                    $('form').on('submit', function(e) {
                        var code = neditor['<?=$notification->ID?>'].getValue();
                        $('[name="alchemyst-forms-notification[<?=$notification->ID?>][email]"]').val(code);

                        var annotations = neditor['<?=$notification->ID?>'].getSession().getAnnotations();

                        var allow_submit = true;

                        if (annotations.length) {
                            $.each(annotations, function(k, v) {
                                if (v.type == "error") {
                                    allow_submit = false;
                                    alert("Contact Form Error (line: " + (v.row + 1) + ")\n\n" + v.text);
                                    return false;
                                }
                            });
                        }
                        return allow_submit;
                    });
                });
            </script>
        </td>
    </tr>
    <tr>
        <th>
            File Attachments<br>
            <small>Comma seperated list of file input names from your form to attach to this email.</small>
        </th>
        <td>
            <textarea type="text" name="alchemyst-forms-notification[<?=$notification->ID?>][files]" placeholder="file1,file2"><?=$notification->files?></textarea>
            <p id="available-file-inputs">
                <strong>Available File Inputs <small>(Click to copy)</small></strong><br>
                <div class="file-field-names"></div>
            </p>
        </td>
    </tr>

    <tr>
        <th>
            Email Template<br>
            <small>
                Select the template that should be used for this notification.<br>
                <a href="https://github.com/candeocreative/alchemyst-forms#adding-new-email-templates" target="_blank">How to add your own templates.</a>
            </small>
        </th>
        <td>
            <select name="alchemyst-forms-notification[<?=$notification->ID?>][template]">
                <?php foreach($email_templates as $filename => $template) :
                    $selected = '';
                    if ($filename == $notification->template)
                        $selected = ' selected="selected"';
                    ?>
                    <option value="<?=$filename?>"<?=$selected?>><?=$template?> (email/<?=$filename?>)</option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>
