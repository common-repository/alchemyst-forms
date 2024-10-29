// Allows for things like var str = "123454321"; str.replaceAll(1, 'a'); // "a2345432a"
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

function htmlEntities(str) {
    return String(str).replaceAll(/&/g, '&amp;').replaceAll(/</g, '&lt;').replaceAll(/>/g, '&gt;').replaceAll(/"/g, '&quot;');
}

function unHtmlEntities(str) {
    return String(str).replaceAll(/&amp;/g, '&').replaceAll(/&lt;/g, '<').replaceAll(/&gt;/g, '>').replaceAll(/&quot;/g, '"');
}

jQuery(document).ready(function($) {
    $('a[data-af-slide-toggle]').on('click', function() {
        var toggle = $(this).attr('data-af-slide-toggle');

        $('p[data-af-slide-toggle="'+toggle+'"]').slideToggle(250);
    });


    // Select all text when clicking on the shortcode (disabled) inputs.
    // TODO: Add a copy button?
    $('#af-form-shortcode input, .alchemyst-forms-click-highlight').on('click focus', function(e) {
        $(this).select();
    });

    // Simple jquery tab view
    $('#alchemyst-forms-tabs a').on('click', function(e) {
        $('#alchemyst-forms-tabs .nav-tab-active').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.alchemyst-forms-tab-section-active').removeClass('alchemyst-forms-tab-section-active');
        $('.alchemyst-forms-tab-section[data-alchemyst-forms-tab-section="' + $(this).attr('href') + '"]').addClass('alchemyst-forms-tab-section-active');
    });

    // Open right section in case of window.location.hash (on the right screen at least)
    if ($('.post-type-alchemyst-forms').length && $('.post-php').length && $('#alchemyst-forms-tabs').length && window.location.hash) {
        $('.nav-tab[href="' + window.location.hash + '"]').click();
    }

    // Up/down arrow switching for expandable sections (notifications)
    function toggle_up_down($el) {
        if ($el.hasClass('dashicons-arrow-down')) {
            $el.removeClass('dashicons-arrow-down');
            $el.addClass('dashicons-arrow-up');
        }
        else {
            $el.removeClass('dashicons-arrow-up');
            $el.addClass('dashicons-arrow-down');
        }
    }

    // Register the collapsable containers
    function register_collapsables() {
        $('.collapse-header').off('click');
        $('.collapse-header').on('click', function(e) {
            $(this).parents('.alchemyst-forms-notification').find('.collapsable').toggleClass('open');
            toggle_up_down($(this).find('.icon-right'));
        });
    }
    register_collapsables();

    // Editors and notification add functionality
    var new_count = 0;
    var new_editors = {};
    $('#alchemyst-forms-add-notification').on('click', function(e) {
        if (!alchemyst_forms_admin_js.active_license && new_count != 0) {
            return false;
        }
        else if (!alchemyst_forms_admin_js.active_license && new_count == 0) {
            $('#alchemyst-forms-add-notification').attr('disabled', true);
            $('#alchemyst-forms-add-notification').css('cursor', 'help');
            $('#alchemyst-forms-add-notification').attr('title', 'Only one notification is allowed with our free version. To enable more email notifications, and more notification types, please purchase a license at http://alchemyst.io');
        }

        var new_id = 'new-' + new_count;
        var html = $('[data-alchemyst-forms-template="' + $('#alchemyst-forms-notification-type').val() + '"]').html();
        html = html.replaceAll('{id}', 'new-' + new_count + '');
        $('#alchemyst-forms-notifications-wrap').append(html);

        if ($('#form-notification-content-editor-' + new_id).length) {
            var editor_theme = (alchemyst_forms_admin_js.ace_theme ? alchemyst_forms_admin_js.ace_theme : 'chrome');
            var margin_column = parseInt(alchemyst_forms_admin_js.editor_preferred_line_length) ? parseInt(alchemyst_forms_admin_js.editor_preferred_line_length) : 999999;
            var font_size = parseInt(alchemyst_forms_admin_js.editor_font_size) ? parseInt(alchemyst_forms_admin_js.editor_font_size) : 12;
            new_editors[new_id] = ace.edit('form-notification-content-editor-' + new_id);
            new_editors[new_id].setTheme('ace/theme/' + editor_theme);
            new_editors[new_id].session.setMode("ace/mode/html");
            new_editors[new_id].setFontSize(font_size);
            new_editors[new_id].setPrintMarginColumn(margin_column);

            $('form').on('submit', function(e) {
                var code = new_editors[new_id].getValue();
                $('[name="alchemyst-forms-notification[' + new_id + '][email]"]').val(code);

                var annotations = new_editors[new_id].getSession().getAnnotations();

                var allow_submit = true;

                if (annotations.length) {
                    $.each(annotations, function(k, v) {
                        if (v.type == "error") {
                            allow_submit = false;
                            alert("Contact Form Error (line: " + (v.row + 1) + ")\n\n" + v.text);
                            return false;
                        }
                    });
                }
                return allow_submit;
            });
        }

        register_collapsables();
        new_count++;
    });

    // Handle notification deletions.
    $('#alchemyst-forms-notifications-wrap .delete-notification').live('click', function(e) {
        if (confirm("Are you sure you want to delete this notification? If you're deleting a new notification this is irreversable. If you are editing an existing notification for this form, this notification will not be retrievable if you click update.")) {
            $(this).parents('.alchemyst-forms-notification').remove();
        }
    });

    // Mostly meant for nav-tabs. Will autoload the correct tab if a window.location.hash matches.
    if (window.location.hash) {
        if ($('#alchemyst-forms-tabs a[href="' + window.location.hash + '"]').length) {
            $('#alchemyst-forms-tabs a[href="' + window.location.hash + '"]').click();
        }
    }

    // Delete Entry ajax clicks
    $('[data-delete-entry]').on('click', function(e) {
        e.preventDefault();
        var $that = $(this);
        var id = $that.attr('data-delete-entry');
        var data = {
            action: 'alchemyst-forms-delete-entry',
            entry_id: id
        };
        $.post(alchemyst_forms_admin_js.ajax_url, data, function (data) {
            // Remove from table
            if (window.entries_table) {
                window.entries_table.row($that.parents('tr')).remove().draw();

                // We should provide an UNDO button incase it was clicked by mistake
                var $notifications = $('.entry-notifications');

                $notifications.html($notifications.html().replace(/\[id\]/g, id).replace(/[0-9]+/g, id));
                $notifications.show();
                register_undo();
            }
            else {
                // Table must not have finished init yet??
                alert("We couldn't remove the row from the entries table as it has not finished loading yet, however, the entry has been sucessfully deleted. Please refresh this page to view the updated list.");
            }
        });
    });

    function register_undo() {
        $('[data-delete-entry-undo]').on('click', function(e) {
            e.preventDefault();
            var $that = $(this);
            var id = $that.attr('data-delete-entry-undo');
            var data = {
                action: 'alchemyst-forms-delete-entry-undo',
                entry_id: id
            };

            $.post(alchemyst_forms_admin_js.ajax_url, data, function (data) {
                location.reload();
            });
        });
    }

    // Bit of a hacky copy to clipboard handler for field names in notifications tabs.
    $('.alchemyst-forms-field-name').live('click', function(e) {
        var $temp = $("<input>")
        $("body").append($temp);
        $temp.val($(this).html()).select();
        document.execCommand("copy");
        $temp.remove();
        return false;
    });

    // Make the dataTable buttons look wordpress-y
    function add_button_classes() {
        $('.dataTables_paginate .paginate_button').addClass('button button-primary button-large');
    }

    if (alchemyst_forms_admin_js.entry_fields) {
        var column_index = -1;

        var field_names = $.map(alchemyst_forms_admin_js.entry_fields, function(value, index) {
            column_index++;
            return {
                name: value,
                targets: column_index,
            };
        });

        alchemyst_forms_admin_js.datatables_settings.columnDefs = field_names;

        //console.log(alchemyst_forms_admin_js.datatables_settings);

        var entries_table = $('.alchemyst-forms-entries-table').on('init.dt', function(e) {
            setTimeout(function() {
                $('[data-alchemyst-forms-tab-section="#entries"]').removeClass('af-tab-section-show-until-init');
            }, 500);
        }).DataTable(alchemyst_forms_admin_js.datatables_settings);
        window.entries_table = entries_table;

        $('.alchemyst-forms-entries-table').on('draw.dt', add_button_classes);
        add_button_classes();

        function toggle_column_visibility(that) {
            var visible = $(that).is(':checked');
            var id = $(that).attr('data-alchemyst-forms-column-toggle');
            var columns = entries_table.settings().init().columnDefs;
            entries_table.columns().every(function(index) {
                var field_id = index;
                var field_name = $(this.header()).attr('data-alchemyst-forms-column-toggle');
                if (field_name == id || field_name.toLowerCase() == id) {
                    entries_table.column(index).visible(visible);
                }
            });
        }

        // initialize column visibility
        $('.alchemyst-forms-column-toggles input').each(function() {
            toggle_column_visibility(this);
        });

        // toggle visibility
        $('.alchemyst-forms-column-toggles input').live('change', function(e) {
            toggle_column_visibility(this);
            save_entry_view_settings();
        });

        entries_table.on('column-reorder', save_entry_view_settings);

        function save_entry_view_settings() {
            var field_order = [];
            entries_table.columns().every(function(index) {
                field_order.push($(this.header()).attr('data-alchemyst-forms-column-toggle'));
            });

            var visible_fields = [];
            $('.alchemyst-forms-column-toggles input').each(function() {
                if ($(this).is(':checked'))
                    visible_fields.push($(this).attr('data-alchemyst-forms-column-toggle'));
            });

            var data = {
                "action": "alchemyst-forms-save-entry-view",
                "visible-fields": visible_fields,
                "field-order": field_order,
                "form_id": alchemyst_forms_admin_js.form_id
            };

            $.post(alchemyst_forms_admin_js.ajax_url, data, function(data) {
                // console.log(data);
            });
        }
    }

    // Build the field names in real time.
    if (typeof(editor) != "undefined") {
        if (editor) {

            function regen_field_shortcodes() {

                var code = editor.getValue();

                // console.log('test', code);

                var names = [];
                $('<div></div>').append($(code)).find('[name]').each(function() {
                    var n = $(this).attr('name');
                    n = n.replace('[]', '');
                    if (names.indexOf(n) == -1 && $(this).attr('type') != 'file') {
                        names.push(n);
                    }
                });

                $('.alchemyst-forms-field-names').html('');

                $.each(names, function(index, value) {
                    $('<a class="alchemyst-forms-field-name" href="#"></a>').html('[' + value + ']').appendTo('.alchemyst-forms-field-names');
                });


                $('.file-field-names').html('');
                $('<div></div>').append($(code)).find('[type="file"]').each(function() {
                    var n = $(this).attr('name');
                    $('<a class="alchemyst-forms-field-name" href="#"></a>').html(n).appendTo('.file-field-names');
                });

                // Hide the paragraph if there are no files...
                if (!$(code).find('[type="file"]').length) {
                    $('p#available-file-inputs').hide();
                }
                else {
                    $('p#available-file-inputs').show();
                }
            }
            regen_field_shortcodes();

            editor.on('blur', regen_field_shortcodes);
        }
    }

    if ($('#input-builder-modal-wrap').length) {
        var $ib = $('#input-builder-modal-wrap');

        $ib.find('.modal-close').click(function(e) {
            $ib.hide();
        });

        $('#input-builder-button').click(function(e) {
            e.preventDefault();

            if ($ib.is(':visible')) {
                $ib.hide();
                $(this).html('Show Input Builder');
            }
            else {
                $ib.show();
                $(this).html('Hide Input Builder');
            }

            return false;
        });

        $(window).resize(function() {
            set_modal_height($ib);
        });
        set_modal_height($ib);

        // does some quick adjustments to vertical modal positioning based on window size.
        function set_modal_height($el) {
            var $modal = $el.find('.modal');
            var modal_height = $modal.outerHeight();
            var window_inner_height = $(window).innerHeight();

            if (modal_height > window_inner_height) {
                $modal.css('top', '0px');
                $modal.css('transform', 'translateX(-50%)');
                $modal.css('margin', '2em 0');

                $el.css('overflow-y', 'scroll');
            }
            else {
                $modal.css('top', '');
                $modal.css('transform', '');
                $modal.css('margin', '');

                $el.css('overflow-y', '');

            }
        }


        var $ibit = $('#ib-input-type');


        $ibit.change(function() {
            var val = $ibit.val().toLowerCase();

            $ib.find('fieldset').each(function() {
                var $that = $(this);
                var parts = $that.attr('data-input-type').split(',');
                if (parts.indexOf(val) === -1 && parts.indexOf('all') === -1) {
                    $that.hide();
                }
                else {
                    $that.show();
                }
            });
        });
        $ibit.change();

        $('#ib-repeat-shell').sortable({
            axis: "y"
        });

        $('#ib-repeat-btn').click(function(e) {
            e.preventDefault();

            var $repeat = $('#ib-repeat-shell').find('.repeat').first().clone();
            $repeat.find('input').val('');
            $repeat.appendTo($('#ib-repeat-shell'));

            $('#ib-repeat-shell').sortable("refresh");

            return false;
        });

        $('#ib-repeat-shell .remove').live('click', function(e) {
            e.preventDefault();

            $(this).parents('.repeat').remove();

            $('#ib-repeat-shell').sortable("refresh");

            return false;
        });

        // The main input builder utility.
        function input_builder(e) {
            var type = $ibit.val().toLowerCase();
            var inputs = ['text','number','range','email','address','tel','date','time','datepicker','wysiwyg','textarea'];
            var use_bootstrap_classes = $('#ib-add-bootstrap-classes').is(':checked');
            var use_fieldset = $('#ib-input-use-fieldset').is(':checked');
            var use_label = $('#ib-input-use-label').is(':checked');
            var inline_display = $('#ib-cb-display-inline').is(':checked');
            var label_text = $('#ib-input-label-text').val();
            var placeholder = $('#ib-input-placeholder').val();

            var name = $('#ib-input-name').val();
            var id = $('#ib-input-id').val();
            var classes = $('#ib-input-class').val();

            var required = $('#ib-required').is(':checked');

            var value = $('#ib-input-default-value').val();

            $builder = $('<div></div>');

            var $input
            if (inputs.indexOf(type) !== -1) {

                if (type == 'textarea') {
                    var $input = $('<textarea>');
                    if (value) $input.html(value);
                }
                else {
                    var $input = $('<input>');
                    $input.attr('type', type);
                    if (value) $input.attr('value', value);
                }

                if (use_bootstrap_classes && type != 'wysiwyg' && type != 'file' && type != 'range') {
                    $input.addClass('form-control');
                }

                if (name) $input.attr('name', name);

                if (id && type != 'wysiwyg') $input.attr('id', id);

                if (required && type != 'wysiwyg') {
                    $input.attr('data-required', 'true');
                }

                if (type != 'wysiwyg') {
                    if (classes) {
                        $.each(classes.split(' '), function(key, value) {
                            $input.addClass(value);
                        });
                    }
                    if (placeholder) $input.attr('placeholder', placeholder);
                }

                if (type == 'number' || type == 'range') {
                    var min = $('#ib-number-min').val();
                    var max = $('#ib-number-max').val();
                    var step = $('#ib-number-step').val();

                    if (min) $input.attr('min', min);
                    if (max) $input.attr('max', max);
                    if (step) $input.attr('step', step);
                }

                if (type == 'text' || type == 'number' || type == 'email' || type == 'tel') {
                    var matches = $('#ib-matches').val();
                    if (matches) $input.attr('data-matches', matches);
                }

                if (type == 'text') {
                    var min_length = $('#ib-min-length').val();
                    var max_length = $('#ib-max-length').val();

                    if (min_length) $input.attr('data-min-length', min_length);
                    if (max_length) $input.attr('data-max-length', max_length);
                }


                if (type == 'datepicker') {
                    var dateformat = $('#ib-input-datepicker-format').val();

                    if (dateformat) $input.attr('data-datepicker-format', dateformat);
                }

                if (use_fieldset) {
                    var $fieldset = $('<fieldset></fieldset>');
                    if (use_bootstrap_classes) $fieldset.addClass('form-group');
                    if (use_label) {
                        var $label = $('<label></label>');
                        if (id && type != 'wysiwyg') $label.attr('for', id);
                        if (label_text) $label.html(label_text);
                        if (required) {
                            $label.append('<span class="alchemyst-required">*</span>');
                        }
                        $fieldset.append($label);
                    }
                    $fieldset.append($input);
                    $input = $fieldset;
                }
                else {
                    if (use_label) {
                        var $label = $('<label></label>');
                        if (id && type != 'wysiwyg') $label.attr('for', id);
                        if (label_text) $label.html(label_text);
                        if (required) {
                            $label.append('<span class="alchemyst-required">*</span>');
                        }
                        $builder.append($label);
                    }
                }

                $builder.append($input);
            }
            else if (type == 'select') {
                var $input = $('<select></select>');

                if (use_bootstrap_classes) $input.addClass('form-control');

                if (name) $input.attr('name', name);
                if (id) $input.attr('id', id);
                if (required) $input.attr('data-required', 'true');
                if (classes) {
                    $.each(classes.split(' '), function(key, value) {
                        $input.addClass(value);
                    });
                }

                $('#ib-repeat-shell .repeat').each(function() {
                    var rval = $(this).find('.repeat-value').val();
                    var rlabel = $(this).find('.repeat-label').val();

                    var $cb = $('<option></option>');
                    $cb.attr('value', rval);
                    $cb.html(rlabel);
                    $cb.appendTo($input);
                });

                if (use_fieldset) {
                    var $fieldset = $('<fieldset></fieldset>');
                    if (use_bootstrap_classes) $fieldset.addClass('form-group');
                    if (use_label) {
                        var $label = $('<label></label>');
                        if (id) $label.attr('for', id);
                        if (label_text) $label.html(label_text);
                        if (required) {
                            $label.append('<span class="alchemyst-required">*</span>');
                        }
                        $fieldset.append($label);
                    }
                    $fieldset.append($input);
                    $input = $fieldset;
                }
                else {
                    if (use_label) {
                        var $label = $('<label></label>');
                        if (id) $label.attr('for', id);
                        if (label_text) $label.html(label_text);
                        if (required) {
                            $label.append('<span class="alchemyst-required">*</span>');
                        }
                        $builder.append($label);
                    }
                }

                $builder.append($input);
            }
            else if (type == 'checkbox' || type == 'radio') {
                var $input = $('<div></div>');
                $('#ib-repeat-shell .repeat').each(function() {
                    var $div = $('<div></div>');
                    var $label = $('<label></label>');
                    var $cb = $('<input>');

                    var rval = $(this).find('.repeat-value').val();
                    var rlabel = $(this).find('.repeat-label').val();

                    if (inline_display) {
                        $label.addClass(type + '-inline');
                    }
                    else {
                        $div.addClass(type);
                    }

                    if (type == 'checkbox') {
                        if (name) $cb.attr('name', name + '[]');
                    }
                    else {
                        if (name) $cb.attr('name', name);
                    }

                    $cb.attr('type', type);
                    $cb.attr('value', rval);
                    $label.html(rlabel);
                    $cb.prependTo($label);
                    if (!inline_display) {
                        $label.appendTo($div);
                        $div.appendTo($input);
                    }
                    else {
                        $label.appendTo($input);
                    }
                });
                $input = $($input.html());

                if (use_fieldset) {
                    var $fieldset = $('<fieldset></fieldset>');
                    if (required) $fieldset.addClass('check-required');
                    if (use_bootstrap_classes) $fieldset.addClass('form-group');

                    $fieldset.append($input);
                    $input = $fieldset;
                }
                else if (required) {
                    $wrap = $('<div></div>');
                    $wrap.addClass('check-required');
                    $wrap.append($input);
                    $input = $wrap;
                }

                $builder.append($input);
            }
            else if (type == 'submit') {
                var $input = $('<input>');
                if (name) $input.attr('name', name);
                $input.attr('type', type);
                if (use_bootstrap_classes) {
                    $input.addClass('btn');
                    $input.addClass('btn-primary');
                }
                if (classes) {
                    $.each(classes.split(' '), function(key, value) {
                        $input.addClass(value);
                    });
                }
                if (id) $input.attr('id', id);

                if (value) $input.attr('value', value);
                $input.appendTo($builder);
            }
            else if (type == 'file') {
                var $input = $('<input>');
                $input.attr('type', 'file');

                var ib_max_file_size = $('#ib-max-file-size').val();
                var ib_allowed_types = $('#ib-allowed-types').val();
                var ib_max_width = $('#ib-max-width').val();
                var ib_max_height = $('#ib-max-height').val();

                if (ib_max_file_size) $input.attr('data-max-file-size', ib_max_file_size);
                if (ib_allowed_types) $input.attr('data-allowed-types', ib_allowed_types);
                if (ib_max_width) $input.attr('data-max-width', ib_max_width);
                if (ib_max_height) $input.attr('data-max-height', ib_max_height);

                if (name) $input.attr('name', name);
                if (id) $input.attr('id', id);

                if (classes) {
                    $.each(classes.split(' '), function(key, value) {
                        $input.addClass(value);
                    });
                }

                if (use_bootstrap_classes) {
                    var $label = $('<label></label>').addClass('file');
                    $input.appendTo($label);

                    var $span = $('<span></span>').addClass('file-custom');
                    $span.appendTo($label);

                    $input = $label;
                }

                if (use_fieldset) {
                    var $fieldset = $('<fieldset></fieldset>');
                    if (use_bootstrap_classes) $fieldset.addClass('form-group');
                    if (use_label) {
                        var $label = $('<label></label>');
                        if (id) $label.attr('for', id);
                        if (label_text) $label.html(label_text);
                        if (required) {
                            $label.append('<span class="alchemyst-required">*</span>');
                        }
                        $fieldset.append($label);
                    }
                    $fieldset.append($input);
                    $input = $fieldset;
                    $builder.append($input);
                }
                else {
                    if (use_label) {
                        var $label = $('<label></label>');
                        if (id) $label.attr('for', id);
                        if (label_text) $label.html(label_text);
                        if (required) {
                            $label.append('<span class="alchemyst-required">*</span>');
                        }
                        $builder.append($label);
                    }
                }

                $builder.append($input);
            }
            else if (type == 'repeatable') {
                var $repeatable = $('<repeatable></repeatable>');

                if (name) $repeatable.attr('name', name);

                var ib_repeater_min = $('#ib-repeater-min').val();
                var ib_repeater_max = $('#ib-repeater-max').val();
                var ib_repeater_add_label = $('#ib-repeater-add-label').val();
                var ib_repeater_minus_label = $('#ib-repeater-minus-label').val();

                if (ib_repeater_min) $repeatable.attr('data-required-count', ib_repeater_min);
                if (ib_repeater_max) $repeatable.attr('data-maximum-count', ib_repeater_max);
                if (ib_repeater_add_label) $repeatable.attr('data-add-label', ib_repeater_add_label);
                if (ib_repeater_minus_label) $repeatable.attr('data-minus-label', ib_repeater_minus_label);

                $repeatable.html("<!-- Insert your fields here. Anything between the repeatable tag will be repeated -->");

                $repeatable.appendTo($builder);
            }

            var html = html_beautify($builder.html());
            $('#ib-result code pre').html(htmlEntities(html));
            set_modal_height($ib);
        }

        // bind events
        $('#input-builder-modal-wrap input, #input-builder-modal-wrap select').live('keyup change', input_builder);
        $('#ib-repeat-btn').click(input_builder);


        // Copy/insert input builder stuff.
        $('#ib-input-builder-copy').click(function(e) {
            var code = $('#ib-result code pre').html();
            var $temp = $("<input>")
            $("body").append($temp);
            $temp.val(unHtmlEntities(code)).select();
            document.execCommand("copy");
            $temp.remove();
            return false;
        });

        $('#ib-input-builder-insert').click(function(e) {
            var code = unHtmlEntities($('#ib-result code pre').html()) + "\n";
            editor.insert(code);
            return false;
        });
    }
});
