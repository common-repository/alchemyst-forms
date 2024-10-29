<?php global $form_id, $alchemyst_form; ?>
<?php do_action('alchemyst_forms:before-form', $form_id, $alchemyst_form); ?>
<form
    method="post"
    class="alchemyst-form"
    enctype="multipart/form-data"
    data-alchemyst-form-id="<?=$form_id?>"
    data-af-ajax-action
>

    <?php do_action('alchemyst_forms:before-form-output', $form_id, $alchemyst_form); ?>

    <?=$alchemyst_form->html?>

    <?php do_action('alchemyst_forms:after-form-output', $form_id, $alchemyst_form); ?>

</form>

<?php do_action('alchemyst_forms:after-form', $form_id, $alchemyst_form); ?>