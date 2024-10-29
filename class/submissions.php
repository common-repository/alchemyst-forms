<?php
/**
 * Handle submissions
 */
use PHPHtmlParser\Dom;

class Alchemyst_Forms_Submissions {

    /**
     * Handle front end form submission.
     * 1. Get the contact form code and build a $dom object for it
     * 2. Validate file uploads.
     * 3. Validate using Alchemyst_Form_Validator
     * 4. Build a response
     *      4.1. If fail, return response.
     * 5. Process form notifications.
     * 6. Save to Database as Entry
     */
    public static function handle_submission($form_id, $request, $files = array()) {

        $dom = Alchemyst_Form::get_dom($form_id);
        do_action('alchemyst_forms:pre-validate-entry', $form_id, $request, $files);

        // Do file validation first.
        $file_results = array();

        // Set up the validator
        $required_fields = Alchemyst_Form::get_required_fields($form_id, $dom);
        $matching_fields = Alchemyst_Form::get_matching_fields($form_id, $dom);
        $min_lengths     = Alchemyst_Form::get_minimum_lengths($form_id, $dom);
        $max_lengths     = Alchemyst_Form::get_maximum_lengths($form_id, $dom);
        $validator = new Alchemyst_Forms_Submission_Validator($required_fields, $matching_fields, $min_lengths, $max_lengths, $dom);
        $confirm = $validator->validate_postdata($request);

        $confirm = apply_filters('alchemyst_forms:validate_entry', $confirm, $form_id, $dom, $request);

        // Ready the result
        if ($confirm !== true) {
            $response = array(
                'error' => true,
                'result' => $confirm,
            );
            return $response;
        }

        $submission_action = get_post_meta($form_id, '_alchemyst_forms-submission-action', true);

        // Build the responses.
        if (!$submission_action || $submission_action == 'show-success-message') {
            $response = array(
                'success' => true,
                'action' => 'show_success_message',
                'request' => $request,
                'message' => Alchemyst_Forms_Messages::get_message('success', $form_id),
                'notifications' => Alchemyst_Forms_Notifications::get_notifications($form_id),
                'files' => $file_results,
            );
        }
        elseif ($submission_action == 'redirect') {
            $redirect_location = get_post_meta($form_id, '_alchemyst_forms-redirect-url', true);

            if (!$redirect_location) {
                $response = array(
                    'error' => true,
                    'result' => array(
                        'field' => false,
                        'message' => Alchemyst_Forms_Messages::get_message('configuration-error')
                    )
                );
            }
            else {
                $redirect_location = apply_filters('alchemyst_forms:submission-redirect-url', $redirect_location, $form_id, $request, $file_results, $dom);
                $response = array(
                    'success' => true,
                    'action' => 'redirect',
                    'redirect' => $redirect_location,
                    'file_results' => $file_results
                );
            }
        }


        // Save to entries
        $request = apply_filters('alchemyst_forms:pre-save-entry', $request, $form_id, $file_results, $dom);
        $entry_id = Alchemyst_Forms_Entries::save_entry($form_id, $request, $file_results, $dom);

        $notification_processor = new Alchemyst_Forms_Notification_Processor($form_id, $request, $file_results, $entry_id, $dom);


        try {
            $notification_processor->do_notifications();
        }
        catch (Exception $e) {
            $response = array(
                'error' => true,
                'result' => array(
                    'field' => false,
                    'message' => $e->getMessage()
                )
            );
        }




        $response = apply_filters('alchemyst_forms:submission-response', $response, $form_id, $request, $file_results, $dom);
        return $response;
    }


}
