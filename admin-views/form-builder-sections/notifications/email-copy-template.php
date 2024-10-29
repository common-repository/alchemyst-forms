<table class="form-table">
    <tr>
        <th>
            To<br>
            <small>Separate multiple addresses with commas.</small>
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[{id}][to]">
        </td>
    </tr>
    <tr>
        <th>
            CC<br>
            <small>Separate multiple addresses with commas.</small>
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[{id}][cc]">
        </td>
    </tr>
    <tr>
        <th>
            BCC<br>
            <small>Separate multiple addresses with commas.</small>
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[{id}][bcc]">
        </td>
    </tr>
    <tr>
        <th>
            From
        </th>
        <td class="notification-inline-fields">
            <input type="text" name="alchemyst-forms-notification[{id}][from_name]" placeholder="Name">
            <input type="text" name="alchemyst-forms-notification[{id}][from]" placeholder="Email Address">
        </td>
    </tr>
    <tr>
        <th>
            Subject
        </th>
        <td>
            <input type="text" name="alchemyst-forms-notification[{id}][subject]">
        </td>
    </tr>
    <tr>
        <th>
            Message Body<br>
            <small>HTML is allowed.</small>
        </th>
        <td>
            <div class="alchemyst-forms-editor-wrap">
                <pre id="form-notification-content-editor-{id}"></pre>
            </div>
            <input type="hidden" name="alchemyst-forms-notification[{id}][email]" value="">
        </td>
    </tr>
    <tr>
        <th>
            File Attachments<br>
            <small>Comma seperated list of file input names from your form to attach to this email.</small>
        </th>
        <td>
            <textarea type="text" name="alchemyst-forms-notification[{id}][files]" placeholder="file1,file2"></textarea>
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
                <?php foreach($email_templates as $filename => $template) : ?>
                    <option value="<?=$filename?>"><?=$template?> (email/<?=$filename?>)</option>
                <?php endforeach; ?>
            </select>
        </td>
    </tr>
</table>
