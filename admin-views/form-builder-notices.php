<?php
    global $post;
    $post_id = $post->ID;
    $validator = new Alchemyst_Forms_Validator($post_id);
    $responses = $validator->validate();

    foreach ($responses as $response) {
        if (isset($response['valid']) && $response['valid'] != 1) :
            ?>
                <div class="notice notice-<?=$response['level_str']?>">
                    <p>
                        <strong><?=ucfirst($response['level_str'])?>:</strong> <?=$response['message']?>
                    </p>
                </div>
            <?php
        endif;
    }
?>
