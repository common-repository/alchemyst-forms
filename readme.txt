=== Alchemyst Forms ===
Tags: forms, contact forms, HTML forms
Requires at least: 4.4.0
Tested up to: 4.6.1
Stable tag: trunk
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

No complicated form builders, simply write HTML and get functional forms. Email notifications and entries database included.


== Description ==

The free version of Alchemyst Forms (the premium version can be acquired at https://alchemyst.io ). Alchemyst Forms lets you create contact forms within Wordpress using nothing other than HTML. Email notifications and an entries database are included.

The free version contains all you need to get simple forms into your WordPress website, including things like reCAPTCHA, email notifications, and an entries database.

Alchemyst Forms was built to be hackable and completely customizable, you can view our documentation at https://alchemyst.io/documentation/alchemyst-forms/ to get started!

Don't know HTML very well? We've included a simple to use input builder to help you generate fully featured and capable HTML form components.

= Upgrade to the pro version for these additional capabilities =
* Priority Support
* Google Analytics Integration
* Unlimited Notifications
* Insert Post Notifications
* Fully featured, searchable, and sortable entries database.
* CSV Entry Export
* Advanced field types, including WYSIWYG, Address, Tel, Number, Range, Datepicker, and more.
* File Uploads
* Encrypted Fields
* ...and more coming soon!

== Installation ==

Install either by uploading through your WP Admin plugins page or uploading directly via FTP to your plugin directory.


== Frequently Asked Questions ==

Most commonly asked questions are answered in the documentation available at https://alchemyst.io/documentation/alchemyst-forms/


== Changelog ==
= v1.1.8 =
* Backwards compatibility patch for PHP 5.3. Tested in 5.3.29.

= v1.1.7 =
Miscellaneous bugfixes
* Possible php warning on get_field_names in notification interpolator
* Heading line spacing in default email template (noticed in gmail render)
* Fixes a bug with insert post notifications template directory

= v1.1.3 =
* Adds a fix to prevent duplicate form entries by disabling the submit button when the form is processing an ajax request.

= v1.1.2 =
Updated EDD SL Updater class. Small test update to verify working.

= v1.1.0 =
* Several filters were added:
* alchemyst_forms:entry-request – Allows modification of the entry before it is saved, but after it is processed through validation.
* alchemyst_forms:postmeta-keys – Allows extensions to register postmeta keys to attach to the alchemyst form post type.
* alchemyst_forms:validate_entry – After base entry validation, allows for extensions to add form validation.
* Several new and improved actions in the output template
* alchemyst_forms:before-form
* alchemyst_forms:before-form-output
* alchemyst_forms:after-form-output
* alchemyst_forms:after-form
* Additional tabs can now place copy-able field names at the top by simple dropping <div class="alchemyst-forms-field-names"></div> at the top of the sections.

== REST API Endpoints ==
* A couple REST API Endpoints have been added.

* /wp-json/alchemyst/forms/v2/form?id=[ID] (WP_REST_Server::READABLE) Get a form object. Returns rendered HTML back to your app.
* /wp-json/alchemyst/forms/v2/form-submit (WP_REST_Server::CREATABLE) Send POSTdata as a response from the HTML that was rendered from reading the form. A response will be passed back.
* More detailed documentation on the REST API will come as it is tested further. Consider the REST API in beta right now.

= v1.0.13 =
* Fixes an admin javascript issue preventing proper usage of the input builder.

= v1.0.12 =
* Fixes a bug with entry views.
* Corrects a link to the export entries tools page.

= v1.0.11 =
* Fixes a bug with file uploads after switch to wp_mail()

= v1.0.10 =
* Fixes a bug with WPEngine’s MU-Plugins styles and a classname conflict with the input builder.

= v1.0.9 =
* Fixes a javascript bug when multiple email notifications were present for a form.

= v1.0.8 =
* Adds the option to include the HTTP_REFERER url in the email request with the [alchemyst-forms-referrer] shorttag.

= v1.0.7 =
* Fixes various bugs with entry views, particularly in large forms.
* Fixes some javascript bugs with repeatable fields.

= v1.0.6 =
* Updated default editor font size to 14px based on community feedback.

= v1.0.5 =
* Rewrote the mail notification handlers to better follow WordPress standards.

= v1.0.4 =
* Fixes a bug where field names with spaces would not be sent over email properly.
* Fixes several more issues with older versions of PHP.

= v1.0.3 =
* Fixes an issue in the Form Validator for PHP 5.4 and lower.

= v1.0.2 =
* Fixes some name conflicts with localized scripts when rendering Alchemyst Forms on the admin side.
* Plugin now supports rendering forms in wp-admin. You must enqueue the scripts to the admin like below in your theme’s functions.php, or in a plugin:
    `if (class_exists('Alchemyst_Forms')) {
        add_action('admin_enqueue_scripts', array('Alchemyst_Forms', 'frontend_scripts'));
    }`
* Note that if you want to use this in a plugin, you should hook this into the alchemyst_forms:init action.
* Fixes a bug with how field names are interpretted in client side field validation.

= v1.0.0 =
Initial public release.
