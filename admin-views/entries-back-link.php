<?php
    global $post;
    $entry = new Alchemst_Forms_Entry(null, $post);
    $form_id = $entry->meta['_alchemyst_forms-form-id'];
?>
<div class="notice notice-info">
    <p>
        <strong><a href="<?=get_edit_post_link($form_id)?>#entries">&laquo; Back to entries list</a></strong>
    </p>
</div>
