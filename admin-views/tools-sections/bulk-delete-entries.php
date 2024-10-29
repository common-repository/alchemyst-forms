<?php global $entries;
if (is_array($entries)) : ?>
<div class="notice notice-success">
    <p>
        <?=count($entries)?> <?=_n('entry has', 'entries have', count($entries), ALCHEMYST_FORMS_TEXTDOMAIN)?> been deleted.
    </p>
</div>
<?php endif; ?>
<p>
    Deletes all entries for a provided form.<br>
    <strong>Note:</strong> if you choose to permanently delete them instead of just moving to trash, you will be unable to recover any entry data. It is strongly advised that you create a backup of your database before doing this.
</p>
<form method="post">
    <table class="form-table">
        <tr>
            <th>
                Form to Delete Entries For
            </th>
            <td>
                <select name="alchemyst-forms-delete-entries-form-id">
                    <?php
                        $args = array(
                            'post_type' => AF_POSTTYPE,
                            'posts_per_page' => -1,
                            'post_status' => 'any',
                        );
                        $posts = get_posts($args);

                        foreach ($posts as $post) :
                            $selected = '';
                            if (isset($_GET['af-bulk-delete-form-id']) && $_GET['af-bulk-delete-form-id'] == $post->ID)
                                $selected = ' selected="selected"';
                            ?>
                            <option value="<?=$post->ID?>"<?=$selected?>><?=$post->post_title?> (ID: <?=$post->ID?>)</option>
                        <?php endforeach;
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th>
                <label for="permanently-delete">
                    Permanently Delete?
                </label>
            </th>
            <td>
                <label for="permanently-delete">
                    <input type="checkbox" id="permanently-delete" name="permanently-delete" value="1">
                    Delete entries permanently instead of moving to trash.
                </label>
            </td>
        </tr>
    </table>
    <input type="submit" class="button button-primary button-large" value="Delete Entries">
</form>
