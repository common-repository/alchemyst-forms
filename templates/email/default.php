<?php
/**
 * Note: Email templates tend to be messy, convoluted, and nasty looking.
 * This approach allows us to use raw PHP in template files.
 *
 * Use this to leverage variables for CSS!
 */

$css['body'] = <<<CSS
    background: #eeeeee;
    font-family: 'Open Sans', arial, sans-serif;
    font-size: 14px;
    line-height: 18px;
    color: #333333;
    padding: 25px 0;
CSS;

$css['h1'] = <<<CSS
    font-size: 24px;
    line-height: 28px;
CSS;

$css['email_wrap'] = <<<CSS
    background: #ffffff;
    box-shadow: 3px 3px 5px rgba(0, 0, 0, 0.15);
    max-width: 600px;
    margin: 20px auto;
    padding: 1em 2em;
    border-radius: 5px;
    box-sizing: border-box;
CSS;

$css['footer'] = <<<CSS
    color: #bbb;
    font-size: 12px;
CSS;

foreach ($css as &$ss) {
    $ss = trim(str_replace("\n","", $ss));
}
?>
<!doctype html>
<html>
<head>
    <link href='https://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
</head>
<body>
    <div style="<?=$css['body']?>">
        <div class="email-wrap" style="<?=$css['email_wrap']?>">
            <h1 style="<?=$css['h1']?>">{heading}</h1>
            {content}
            <p style="<?=$css['footer']?>">
                <?php
                    $default_footer = "&copy;" . date('Y') . " " . get_bloginfo('name') . ". All Rights Reserved.";
                    $footer_text = apply_filters('alchemyst_forms:default-email-template-footer-text', $default_footer);
                    print($footer_text);
                ?>
            </p>
        </div>
    </div>
</body>
</html>