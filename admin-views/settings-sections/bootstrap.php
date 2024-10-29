<p>
    Alchemyst forms uses a very stripped down version of Twitter's Bootstrap 4 frontend style framework.
</p>

<p>
    The following bootstrap components are available within your forms:
    <ul style="list-style: disc; margin-left: 2em;">
        <li>
            Bootstrap 4's Native (Non-flexbox) grid.
        </li>
        <li>
            All Form Components
        </li>
        <li>
            Alerts
        </li>
        <li>
            Buttons
        </li>
    </ul>
</p>

<p>
    <a href="http://v4-alpha.getbootstrap.com/getting-started/introduction/" target="_blank">Bootstrap 4 Documentation</a><br>
    <a href="https://alchemyst.io/documentation/alchemyst-forms/customizing-disabling-form-styles/">Instructions on Writing Your Own Bootstrap SCSS Styles</a>
</p>

<table class="form-table">
    <tr>
        <th>
            Enable Bootstrap Styles?<br>
        </th>
        <td>
            <?php
                $enable_bootstrap_styles = Alchemyst_Forms_Settings::get_setting('enable-bootstrap-styles');
            ?>
            <input type="hidden" name="enable-bootstrap-styles" value="0">
            <label for="enable-bootstrap-styles">
                <input type="checkbox" name="enable-bootstrap-styles" id="enable-bootstrap-styles" value="1"<?=($enable_bootstrap_styles == '1' ? ' checked="checked"' : null)?>> Enable Bootstrap Styles
            </label>
            <p>
                <small>
                    Disable bootstrap styles if you'd prefer to write your own front end template styles.<br>
                </small>
            </p>
        </td>
    </tr>
</table>
