<?php
/**
 * Process and perform the notifications
 */
class Alchemyst_Forms_Notification_Processor {
    private $notifications; // array of Alchemst_Forms_Notification objects
    private $request; // $_REQUEST from form submission that triggered this notification processor
    private $dom; // $dom from Alchemyst_Form::get_dom();

    function __construct($form_id, $request, $files, $entry_id, $dom) {
        $this->notifications = Alchemyst_Forms_Notifications::get_notifications($form_id);
        $this->request = $request;
        $this->files = $files;
        $this->entry_id = $entry_id;
        $this->dom = $dom;
    }

    /**
     * Do notifications.
     *
     * Adds a filter for each notification based on the notification ID -
     * add_filter('alchemyst_forms:do-notification:1', 'callback', 10, 3);
     *
     * Filter params: $notification - notification object
     *                $request - submission request
     *                $dom - html parsed DOM object.
     */
    public function do_notifications() {

        foreach ($this->notifications as $notification) {
            $do_notification = apply_filters('alchemyst_forms:do-notification:' . $notification->ID, true, $notification, $request, $dom);

            if ($do_notification) {
                $this->route_notification($notification);
            }
        }
    }

    /**
     * Route notifications to the right functions.
     * At this point the only thing this plugin guarantees is that $notification->type is set.
     *
     * @param $notification - Notification object to
     */
    private function route_notification($notification) {
        switch ($notification->type) {
            case 'email':
                $this->do_email_notification($notification);
                break;
            default:
                # code...
                do_action('alchemyst_forms:do-notification-type:' . $notification->type, $notification, $this->request, $this->dom);
                break;
        }
    }

    /**
     * Do email notification
     *
     * @param $notification - Alchemst_Forms_Notification object that has been checked to have $notification->type = 'email';
     */
    private function do_email_notification($notification) {
        // Set up an array of variables to interpolate with form fields.
        $to_interpolate['to'] = $notification->to;
        $to_interpolate['cc'] = $notification->cc;
        $to_interpolate['bcc'] = $notification->bcc;
        $to_interpolate['subject'] = $notification->subject;
        $to_interpolate['from'] = $notification->from;
        $to_interpolate['from_name'] = $notification->from_name;
        $to_interpolate['email_body'] = $notification->email;

        $to_interpolate = apply_filters('alchemyst_forms:email-notification-settings', $to_interpolate, $notification);

        $interpolated_vars = Alchemyst_Forms_Interpolator::interpolate_vars($to_interpolate, $notification->form_id, $this->request, $this->dom);

        extract($interpolated_vars);

        // Do file attachments.
        $attachments = array();
        if (!empty($this->files)) {
            $notification_files = array_map('trim', explode(',', $notification->files));


            foreach ($this->files as $file) {
                if (in_array($file['field'], $notification_files)) {
                    $attachments[] = $file['file'];
                }
            }
        }

        // Send it!
        $email = new Alchemyst_Forms_Email($notification->template);
        $email->to($to);
        $email->cc($cc);
        $email->bcc($bcc);
        $email->from($from);
        $email->from_name($from_name);
        $email->subject($subject);
        $email->heading($subject); // May or may not be used by the template.
        $email->content($email_body);
        $email->attachments($attachments);

        $sent_message = $email->send();
    }
}
