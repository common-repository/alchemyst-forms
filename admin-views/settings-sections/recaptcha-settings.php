<p>
    Using reCAPTCHA will add an extra layer of spam protection to your forms, and may significantly
    reduce the amount of spam you receive.
</p>

<p>
    You can sign up for a keypair at
    <a href="https://www.google.com/recaptcha/" target="_blank">https://www.google.com/recaptcha/</a>
    <span class="dashicons dashicons-external"></span>
</p>

<p>
    To use reCAPTCHA in your forms, simply place <code>{recaptcha}</code> somewhere in your form
    (typically right before the submit button).
</p>

<table class="form-table">
    <tr>
        <th>
            Site Key
        </th>
        <td>
            <?php
                $recaptcha_site_key = Alchemyst_Forms_Settings::get_setting('recaptcha-site-key');
            ?>
            <input
                type="text"
                name="recaptcha-site-key"
                id="recaptcha-site-key"
                value="<?=$recaptcha_site_key?>"
            >
        </td>
    </tr>
    <tr>
        <th>
            Secret Key
        </th>
        <td>
            <?php
                $recaptcha_secret_key = Alchemyst_Forms_Settings::get_setting('recaptcha-secret-key');
            ?>
            <input
                type="password"
                name="recaptcha-secret-key"
                id="recaptcha-secret-key"
                value="<?=$recaptcha_secret_key?>"
            >
        </td>
    </tr>
</table>
