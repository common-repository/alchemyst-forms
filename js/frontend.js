(function($) {
    $.fn.hasAttr = function( name ) {
        for (var i = 0, l = this.length; i < l; i++) {
            if (!!( this.attr( name ) !== undefined )) {
                return true;
            }
        }
        return false;
    };
})(jQuery);

String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, 'g'), replacement);
};

// Candeo Ajax Forms tools
// rewrite this as a jQuery extension?
jQuery(document).ready(function($) {

    // Init datepickers
    $('[data-datepicker-field]').each(function() {
        var $that = $(this);
        var format = $that.attr('data-datepicker-format');
        if (!format) format = "MM d, yy";
        $that.datepicker({dateFormat: format});
    });

    // This is actually the speed for every animation called in this file.
    var validation_speed = 250;

    /**
     * Validate an email address to see if it looks acceptable.
     * Nearly all emails should pass this check but it probably doesn't cover 100% of the valid email address namespace
     *
     * @var email - Email address to test
     * @return - true if email looks valid, false otherwise.
     */
    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    }

    function ucwords(str) {
        return (str + '')
        .replace(/^([a-z\u00E0-\u00FC])|\s+([a-z\u00E0-\u00FC])/g, function ($1) {
          return $1.toUpperCase()
        })
    }

    function interpret_field_name(name) {
        name = name.replaceAll('[]', '');
        name = name.replaceAll('-' , ' ');
        name = name.replaceAll('_' , ' ');
        name = ucwords(name);
        name = '"' + name + '"';
        return name;
    }

    /**
     * Validate the fields. Called on blur and keyup, as well as on form submission
     * Translations are available on the global af_translations and are localized through WP.
     *
     * @var $el - jQuery object of the current input being validated (or textarea)
     */
    function validate_field($el) {
        if ($el.attr('type') == 'checkbox' || $el.attr('type') == 'radio') {
            return true;
        }
        if ($el.hasAttr('data-required') && !($el.val().length)) {
            var message = af_translations.required_field;
            var name = interpret_field_name($el.attr('name'));
            message = message.replace(/\[field\]/g, name);
            show_validation_message($el, message);
            $el.addClass('validate-failed');
            return false;
        }
        else if ($el.attr('type') == 'email' && !(validateEmail($el.val()))) {
            var message = af_translations.invalid_email;
            show_validation_message($el, message);
            $el.addClass('validate-failed');
            return false;
        }
        else {
            $el.removeClass('validate-failed');

            if (!($el.parents('form').find('.validate-failed').length))
                hide_validation_message($el);
        }

        return true;
    }

    /**
     * Validate checkfields (also radios)
     * Only called on form submission.
     *
     * @var $el - A jQuery object for the parent form-group containing this checkfield.
     */
    function validate_checkfield($el) {
        var has_selection = false;
        $el.find('input[type="checkbox"], input[type="radio"]').each(function() {
            if ($(this).is(":checked"))
                has_selection = true;
        });

        if (!has_selection)
            show_checkfield_validation_message($el, 'Please fill in the required fields');
        else
            hide_checkfield_validation_message($el);

        return has_selection;
    }

    /**
     * Generate a random string containing any alpha-numeric character to a set length.
     * @var length - Length of the string you wish to get back.
     */
    function random_string(length) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for( var i=0; i < length; i++ )
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }

    /**
     * Show/hide validation message handlers
     * TODO: Improve these functions and replace manual validation stuff below.
     *
     * @var $el - Some jQuery object within the form.
     * @var msg - Message to display. Can contain HTML tags.
     */
    function show_validation_message($el, msg) {
        $el.parents('form').find('.alchemyst-forms-success-validation').hide();
        $el.parents('form').find('.alchemyst-forms-validation').html(msg).fadeIn(validation_speed);
    }
    function hide_validation_message($el) {
        $el.parents('form').find('.alchemyst-forms-validation').fadeOut(validation_speed);
        $el.parents('form').find('input[type="submit"]').removeAttr('disabled');
    }
    function show_checkfield_validation_message($el, msg) {
        $el.parents('form').find('.alchemyst-forms-validation').html(msg);
        $el.parents('form').find('.alchemyst-forms-validation').fadeIn(validation_speed);
        $el.addClass('has-danger');
        $el.find('input').addClass('form-control-danger');
    }
    function hide_checkfield_validation_message($el) {
        $el.removeClass('has-danger');
        $el.find('input').removeClass('form-control-danger');
        $($el).parents('form').find('.alchemyst-forms-validation').fadeOut(validation_speed);
        $($el).parents('form').find('input[type="submit"]').removeAttr('disabled');
    }

    /**
     * Validate fields on blur (lose focus) - useful for client side validation without being too annoying.
     */
    $('[data-af-ajax-action] input, [data-af-ajax-action] textarea, [data-af-ajax-action] select').live('blur', function() {
        if (
            $(this).attr('type') != "submit" &&
            $(this).attr('type') != "button" &&
            !$(this).hasAttr('data-datepicker-field')
        )
            validate_field($(this));
    });

    /**
     * Validate fields on keyup. Might be worth disabling this in some instances. Could be annoying particularly in
     * cases where forms contain email fields since the emails will almost certainly not validate properly.
     *
     * Actually thats why we're not even checking email fields. Still...
     */
    $('[data-af-ajax-action] input').live('keyup', function() {
        if (
            $(this).attr('type') != "submit" &&
            $(this).attr('type') != "button" &&
            $(this).attr('type') != "email"  &&
            $(this).val().length >= 1
        )
            validate_field($(this));
    });

    /**
     * Enable inputmasks
     */
    $('[data-inputmask]').each(function() {
        $(this).inputmask($(this).attr('data-inputmask'));
    });

    /**
     * Previous section button handlers. No validation needed here, just fade out current section, fade in previous.
     * requires [data-section-id="#"] to be available on all forms on the page
     *
     * Section forms do not play nice with ajax img forms at this point. Reason being - ajax image forms need to be
     * in their own form, so for longer forms with complex validation structures, we cannot do ajax file uploads in the
     * middle of the form without validating the whole form at the same time (without unnecessarily complicated server
     * side logic)
     */
    $('[data-section-previous]').on('touchstart click', function(e) {
        e.preventDefault();

        var $el = $(this).parents('form');
        var prevsection = parseInt($el.attr('data-section-id')) - 1;
        $el.fadeOut(validation_speed, function() {
            $('html, body').stop().animate({
                scrollTop: 0
            }, validation_speed);
            $('[data-section-id="' + (prevsection) + '"]').fadeIn(validation_speed);
        });

        return false;
    });

    /**
     * Just a jQuery.each call to register repeater fields below.
     */
    $('.alchemyst-forms-repeater-field').each(function() {
        register_repeater_field($(this));
    });

    /**
     * Register repeater fields to be used with ACF Repeater fields.
     * Registers associated event handlers, tracks how many fields are present.
     *
     * @var $el - jQuery object of the current form.
     */
    function register_repeater_field($el) {
        return false;
    }

    function enumerate_repeater_rows($el) {
        return false;
    }

    /**
     * Handle the [data-af-ajax-action] form submissions. Expects the ajax endpoint to be in the webroot /ajax/ folder.
     * Will check for tinyMCE if it exists and force a save if it is
     *
     * Handles some client side validation before passing along to server. (VALIDATE SERVER SIDE AS WELL). This is a UX feature.
     */
    $('[data-af-ajax-action]').live('submit', function(e) {
        e.preventDefault();

        // TinyMCE is not 100% consistent on when it chooses to save, particularly if you dont edit any other fields
        if (typeof tinyMCE !== 'undefined') {
            tinyMCE.triggerSave();
        }

        var $el = $(this);
        $el.trigger('alchemyst_forms:submission_start');
        $('body').addClass('af-ajax-form-loading');
        $el.addClass('af-ajax-form-loading');

        var serialized_form = $el.serialize();
        var data = new FormData();

        var serializearray = $el.serializeArray();
        $.each(serializearray, function(key, value) {
            data.append(this.name, this.value);
        });

        $el.find('input[type="file"]').each(function() {
            var fname = $(this).attr('name');
            // Multiselect is not yet supported, but maybe we can figure that out later
            // TODO: Support multiselect
            $.each(this.files, function (key, value) {
                data.append(fname, value);
            });
        });

        var any_errors = false;

        $el.find('input, textarea, select').each(function() {
            if (!validate_field($(this)))
                any_errors = true;
        });

        $el.find('.check-required').each(function() {
            if (!validate_checkfield($(this)))
                any_errors = true;
        });

        if (!any_errors) {
            $el.trigger('alchemyst_forms:ajax_start');

            $.ajax({
                url: alchemyst_forms_js.ajax_url,
                type: 'POST',
                data: data,
                cache: false,
                processData: false,
                contentType: false,
                dataType: 'json'
            })
            .done(function(response, textStatus, jqXHR) {
                if (response.error) {
                    show_validation_message($el.find('input'), response.result.message);

                    var scrollto = $el.find('.alchemyst-forms-validation').offset().top;
                    if ($('.admin-bar').length) scrollto = scrollto - $('#wpadminbar').height();

                    $('html, body').stop().animate({
                        scrollTop: scrollto
                    }, validation_speed);

                    if (response.result.field) {
                        $el.find('[name="' + response.result.field + '"]').addClass('validate-failed');
                    }
                } else {
                    hide_validation_message($el.find('input'));
                    process_success(response, $el);
                }
            })
            .fail(function(jqxhr, textStatus, errorThrown) {
                var data = jqxhr.responseJSON;
                console.log('[Alchemyst]', data);
                console.log('[Alchemyst]', jqxhr);
                console.log('[Alchemyst]', textStatus);
                console.log('[Alchemyst]', errorThrown);

                alert('Something went wrong, try again later.');
            })
            .always(function() {
                $('body').removeClass('af-ajax-form-loading');
                $el.removeClass('af-ajax-form-loading');
                $el.trigger('alchemyst_forms:ajax_end');
            });
        } else {
            $('body').removeClass('af-ajax-form-loading');
            $el.removeClass('af-ajax-form-loading');

            var scrollto = $el.find('.alchemyst-forms-validation').offset().top;
            if ($('.admin-bar').length)
                scrollto = scrollto - $('#wpadminbar').height();
            $('html, body').stop().animate({
                scrollTop: scrollto
            }, validation_speed);
        }

        return false;
    });


    // Disable the submit button when ajax is working
    $('[data-af-ajax-action]').on('alchemyst_forms:ajax_start', function() {
        $(this).find('[type="submit"]').attr('disabled', true);
    });

    // Re-enable the submit button when ajax is done
    $('[data-af-ajax-action]').on('alchemyst_forms:ajax_end', function() {
        $(this).find('[type="submit"]').attr('disabled', false);
    });


    /**
     * Process ajax success states. The following response actions are available
     *  load_results - Display the results in the response.target container.
     *  redirect - Redirect the current window to response.redirect instantly.
     *  section - Load the returned response.section form on the same page. Will likely be used along side submit_all_on_page
     *  show_success_message - Show just a validation message for the form,
     *      or in the case of forms with ajax file uploaders, show the message in the first form on the page.
     *      (file upload + section forms is not supported at this time)
     *  submit_all_on_page - Useful after using section responses with [data-section-id] forms.
     *
     * @var response - the Ajax response. Generally a json encoded object containing a response.action and other fun stuff.
     * @var $el - A jQuery object of the parent form which received the ajax request. Useful for showing validation messages.
     */
    function process_success(response, $el) {
        $el.trigger('alchemyst_forms:submission_success', response, $el);


        switch (response.action) {
            case 'show_success_message':
                $('html, body').stop().animate({
                    scrollTop: $el.offset().top
                }, validation_speed);

                if ($('[data-ajax-file-action]').length) {
                    // Show the validation in the first
                    $('[data-ajax-file-action]').first().find('.alchemyst-forms-success-validation').html(response.message).fadeIn(validation_speed);
                }
                else {
                    $el.find(':not(.alchemyst-forms-success-validation)').fadeOut(validation_speed);
                    $el.find('.alchemyst-forms-success-validation').html(response.message).fadeIn(validation_speed);
                }
                if (response.redirect) {
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 3000);
                }

                break;
            case 'redirect':
                if (response.redirect) {
                    window.location.href = response.redirect;
                }
                break;
            default:
                $el.find('.alchemyst-forms-validation').html("<strong>Error!</strong> Your submission was received but we encountered an unknown error along the way.").fadeIn(validation_speed);
                console.log('[Alchemyst]', 'No response action specified from ajax controller.');
                break;
        }
    }
});

// This is a callback from a footer script. We have to place this outside of the global document.ready JS or it wont work
function initAFAutocomplete() {
    jQuery(document).ready(function($) {
        // Create the autocomplete object, restricting the search to geographical
        // location types.
        var autocompletes = [];
        $('[data-address-field]').each(function() {
            var autocomplete = new google.maps.places.Autocomplete(
                this,
                {types: ['geocode']}
            );
            autocompletes.push(autocomplete);
        });
    });
}
