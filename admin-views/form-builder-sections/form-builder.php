<?php
    global $post;
    $screen = get_current_screen();
    $cform_code = htmlentities(get_post_meta($post->ID, '_alchemyst_forms_contact-form-code', true));
?>

<?php if ($screen->action == "add") : ?>
<p>
    <strong>Create your form below using standard HTML!</strong> Any HTML will work to create your forms. We'll worry about parsing your HTML and making it functional behind the scenes!
</p>
<?php endif; ?>

<p>
    <strong>Need help?</strong><br>
    <a href="https://alchemyst.io/documentation/alchemyst-forms/" target="_blank">View the Full Documentation</a><br>
    <a href="https://alchemyst.io/forms/examples/" target="_blank">View Example Forms</a>
</p>

<?php include('input-builder.php'); ?>

<h3>Form:</h3>
<div class="alchemyst-forms-editor-wrap">
    <pre id="form-editor"><?=$cform_code?></pre>
</div>
<input type="hidden" name="contact-form-code" value="">
<script>
    var editor = ace.edit('form-editor');
    var editor_theme = (alchemyst_forms_admin_js.ace_theme ? alchemyst_forms_admin_js.ace_theme : 'chrome');
    editor.setTheme("ace/theme/" + editor_theme);
    editor.session.setMode("ace/mode/html");
    var margin_column = parseInt(alchemyst_forms_admin_js.editor_preferred_line_length) ? parseInt(alchemyst_forms_admin_js.editor_preferred_line_length) : 999999;
    editor.setPrintMarginColumn(margin_column);
    var font_size = parseInt(alchemyst_forms_admin_js.editor_font_size) ? parseInt(alchemyst_forms_admin_js.editor_font_size) : 12;
    editor.setFontSize(font_size);

    jQuery(document).ready(function($) {
        $('form').on('submit', function(e) {
            var code = editor.getValue();
            $('[name="contact-form-code"]').val(code);

            var annotations = editor.getSession().getAnnotations();

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
