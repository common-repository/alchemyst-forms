<?php
$license = Alchemyst_Forms_License::get_instance();
$field_types = array(
    'text' => array(
        'label' => 'Text',
        'pro' => false
    ),
    'textarea' => array(
        'label' => 'Textarea',
        'pro' => false
    ),
    'wysiwyg' => array(
        'label' => 'Wysiwyg',
        'pro' => true
    ),
    'number' => array(
        'label' => 'Number',
        'pro' => true
    ),
    'range' => array(
        'label' => 'Range',
        'pro' => true
    ),
    'email' => array(
        'label' => 'Email',
        'pro' => true
    ),
    'tel' => array(
        'label' => 'Tel',
        'pro' => true
    ),
    'address' => array(
        'label' => 'Address',
        'pro' => true
    ),
    'date' => array(
        'label' => 'Date',
        'pro' => true
    ),
    'time' => array(
        'label' => 'Time',
        'pro' => true
    ),
    'datepicker' => array(
        'label' => 'Datepicker',
        'pro' => true
    ),
    'select' => array(
        'label' => 'Select',
        'pro' => false
    ),
    'checkbox' => array(
        'label' => 'Checkbox',
        'pro' => false
    ),
    'radio' => array(
        'label' => 'Radio',
        'pro' => false
    ),
    'file' => array(
        'label' => 'File Upload',
        'pro' => true
    ),
    'repeatable' => array(
        'label' => 'Repeatable Section',
        'pro' => true
    ),
    'submit' => array(
        'label' => 'Submit',
        'pro' => false
    )
);
?>

<p>
    <a href="#" class="button button-primary button-large" id="input-builder-button">Show Input Builder</a>
</p>

<div id="input-builder-modal-wrap" style="display: none;">
    <h3>Input Builder</h3>
    <fieldset data-input-type="all">
        <label for="ib-input-type">Input Type</label>
        <select name="ib-input-type" id="ib-input-type">
            <?php foreach ($field_types as $type => $field_type) : ?>
                <?php if (!($field_type['pro'] && !$license->license_is_valid())) : ?>
                    <option value="<?=$type?>"><?=$field_type['label']?></option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>
    </fieldset>

    <fieldset data-input-type="all">
        <label for="ib-input-name">Input Name</label>
        <input type="text" name="ib-input-name" id="ib-input-name">
    </fieldset>

    <fieldset data-input-type="text,textarea,number,range,email,address,tel,date,time,datepicker,select,checkbox,radio,file,submit">
        <label for="ib-input-id">ID Attribute</label>
        <input type="text" name="ib-input-id" id="ib-input-id">
    </fieldset>

    <fieldset data-input-type="text,textarea,number,email,address,tel,date,time,datepicker,select,checkbox,radio,file,submit">
        <label for="ib-input-class">Class Names</label>
        <input type="text" name="ib-input-class" id="ib-input-class" placeholder="separate classes with spaces">
    </fieldset>

    <fieldset data-input-type="text,textarea,number,email,address,tel,date,time,datepicker">
        <label for="ib-input-placeholder">Input Placeholder</label>
        <input type="text" name="ib-input-placeholder" id="ib-input-placeholder">
    </fieldset>

    <fieldset data-input-type="text,textarea,wysiwyg,number,range,address,tel,date,time,datepicker,submit">
        <label for="ib-input-default-value">Default Value</label>
        <input type="text" name="ib-input-default-value" id="ib-input-default-value">
    </fieldset>

    <fieldset data-input-type="number,range">
        <label for="ib-number-min">Min</label>
        <input type="number" step="1" name="ib-number-min" id="ib-number-min">
    </fieldset>

    <fieldset data-input-type="number,range">
        <label for="ib-number-max">Max</label>
        <input type="number" step="1" name="ib-number-max" id="ib-number-max">
    </fieldset>

    <fieldset data-input-type="number,range">
        <label for="ib-number-step">Step</label>
        <input type="number" min="0" name="ib-number-step" id="ib-number-step">
    </fieldset>

    <fieldset data-input-type="repeatable">
        <label for="ib-repeater-min">Minimum Count</label>
        <input type="number" min="0" step="1" name="ib-repeater-min" id="ib-repeater-min">
    </fieldset>

    <fieldset data-input-type="repeatable">
        <label for="ib-repeater-max">Maximum Count</label>
        <input type="number" min="0" step="1" name="ib-repeater-max" id="ib-repeater-max">
    </fieldset>

    <fieldset data-input-type="repeatable">
        <label for="ib-repeater-add-label">Add Row Label</label>
        <input type="text" name="ib-repeater-add-label" id="ib-repeater-add-label">
    </fieldset>

    <fieldset data-input-type="repeatable">
        <label for="ib-repeater-minus-label">Remove Row Label</label>
        <input type="text" name="ib-repeater-minus-label" id="ib-repeater-minus-label">
    </fieldset>

    <h3>Formatting Options</h3>

    <fieldset data-input-type="repeatable">
        <em>No formatting options are available</em>
    </fieldset>

    <fieldset data-input-type="text,textarea,wysiwyg,number,email,address,tel,date,time,datepicker,select,checkbox,radio,submit,file">
        <label for="ib-add-bootstrap-classes">Add Bootstrap Classes</label>
        <input type="checkbox" name="ib-add-bootstrap-classes" id="ib-add-bootstrap-classes" <?=(Alchemyst_Forms_Settings::get_setting('enable-bootstrap-styles') == 1 ? 'checked="checked"' : null)?>>
    </fieldset>

    <fieldset data-input-type="text,textarea,wysiwyg,number,range,email,address,tel,date,time,datepicker,select,checkbox,radio,file">
        <label for="ib-input-use-fieldset">Use Fieldset</label>
        <input type="checkbox" name="ib-input-use-fieldset" id="ib-input-use-fieldset">
    </fieldset>

    <fieldset data-input-type="text,textarea,wysiwyg,number,range,email,address,tel,date,time,datepicker,select,file">
        <label for="ib-input-use-label">Use Label</label>
        <input type="checkbox" name="ib-input-use-label" id="ib-input-use-label">
    </fieldset>

    <fieldset data-input-type="text,textarea,wysiwyg,number,range,email,address,tel,date,time,datepicker,select,file">
        <label for="ib-input-label-text">Label Text</label>
        <input type="text" name="ib-input-label-text" id="ib-input-label-text">
    </fieldset>

    <fieldset data-input-type="checkbox,radio">
        <label for="ib-cb-display-inline">Display Inline</label>
        <input type="checkbox" name="ib-cb-display-inline" id="ib-cb-display-inline">
    </fieldset>

    <fieldset data-input-type="select,checkbox,radio" id="ib-repeat-builder">
        <h3>Values</h3>
        <div id="ib-repeat-shell">
            <div class="repeat">
                <span class="repeat-handle dashicons dashicons-menu"></span>
                <input class="repeat-value" type="text" placeholder="Value">
                <input class="repeat-label" type="text" placeholder="Label">
                <a href="#" class="remove">Remove</a>
            </div>
        </div>
        <a id="ib-repeat-btn" class="button button-primary" href="#">Add Value</a>
    </fieldset>

    <fieldset data-input-type="datepicker">
        <label for="ib-input-datepicker-format">Date Format <small><a href="http://api.jqueryui.com/datepicker/#utility-formatDate" target="_blank">(Need help?)</a></small></label>
        <input type="text" name="ib-input-datepicker-format" id="ib-input-datepicker-format" value="MM d, yy">
    </fieldset>

    <h3>Validation Options</h3>

    <fieldset data-input-type="range,submit,wysiwyg,repeatable">
        <em>No validation methods are available.</em>
    </fieldset>

    <fieldset data-input-type="text,textarea,number,email,address,tel,date,time,datepicker,select,checkbox,radio">
        <label for="ib-required">Required</label>
        <input type="checkbox" name="ib-required" id="ib-required">
    </fieldset>

    <fieldset data-input-type="text,number,email,tel">
        <label for="ib-matches">Matches Field</label>
        <input type="text" name="ib-matches" id="ib-matches" placeholder="other field's name">
    </fieldset>

    <fieldset data-input-type="text">
        <label for="ib-min-length">Min Length</label>
        <input type="number" step="1" min="0" name="ib-min-length" id="ib-min-length">
    </fieldset>

    <fieldset data-input-type="text">
        <label for="ib-max-length">Max Length</label>
        <input type="number" step="1" min="0" name="ib-max-length" id="ib-max-length">
    </fieldset>

    <fieldset data-input-type="file">
        <label for="ib-max-file-size">Maximum File Size (in bytes)</label>
        <input type="number" min="0" step="1" name="ib-max-file-size" id="ib-max-file-size">
    </fieldset>

    <fieldset data-input-type="file">
        <label for="ib-allowed-types">Allowed File Types</label>
        <input type="text" name="ib-allowed-types" id="ib-allowed-types" placeholder="separate file types with commas">
    </fieldset>

    <fieldset data-input-type="file">
        <label for="ib-max-width">Maximum Image Width (in pixels)</label>
        <input type="number" min="0" step="1" name="ib-max-width" id="ib-max-width">
    </fieldset>

    <fieldset data-input-type="file">
        <label for="ib-max-height">Maximum Image Height (in pixels)</label>
        <input type="number" min="0" step="1" name="ib-max-height" id="ib-max-height">
    </fieldset>



    <div style="clear: both;"></div>
    <div id="ib-result">
        <code><pre class="prettyprint lang-html"><?=htmlentities('<input type="text" class="form-control">');?></pre></code>

        <a id="ib-input-builder-copy" href="#" class="button button-primary button-large">Copy Code</a>
        <a id="ib-input-builder-insert" href="#" class="button button-primary button-large">Insert Into Form</a>
        <a href="#" class="button button-primary button-large"><input id="ib-input-builder-reset" class="button button-primary" type="reset" value="Reset"></a>
    </div>
</div>
