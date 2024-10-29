<?php
    global $post;
?>
<h4>Shortcode Usage:</h4>
<input type="text" readonly value='[alchemyst-form id="<?=$post->ID?>"]'>

<h4>PHP Usage:</h4>
<input type="text" readonly value="echo do_shortcode('[alchemyst-form id=&quot;<?=$post->ID?>&quot;]');">
