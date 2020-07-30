<?php

class RestMessage extends WP_REST_Controller
{
    /**
     * class instance.
     *
     * @see getInstance()
     * @type object
     */
    protected static $instance = null;

    /**
     * Access class working instance
     *
     * @return  object of this class
     */
    public static function getInstance()
    {
        null === self::$instance and self::$instance = new self;

        return self::$instance;
    }

    /**
     * Register Rest API routes
     *
     * @return void
     */
    public function registerRoutes()
    {
        register_rest_route('contact-form', 'message', array(
            'methods' => 'POST',
            'callback' => array($this, 'handleFormSubmit'),
        ));
    }

    /**
     * Handle form submit action
     *
     * @param WP_REST_Request $data
     * @return mixed
     */
    public function handleFormSubmit(WP_REST_Request $request)
    {
        $nonce = $request->get_param('nonce') ?? '';
        if (!wp_verify_nonce($nonce, 'cf-submit-form')) {
            return new WP_Error(
                'invalid_nonce',
                __('Something went wrong, Please refresh the page.', 'contact_form'),
                array( 'status' => 400 )
            );
        }

        $name = $request->get_param('name') ?? '';
        if (empty($name) || mb_strlen($name)>30) {
            return new WP_Error(
                'invalid_name',
                __('Please enter your name.', 'contact_form'),
                array( 'status' => 400 )
            );
        }

        $email = $request->get_param('email') ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new WP_Error(
                'invalid_email',
                __('Please enter your email address.', 'contact_form'),
                array( 'status' => 400 )
            );
        }

        $message = $request->get_param('message') ?? '';
        if (empty($message)) {
            return new WP_Error(
                'empty_message',
                __('Please enter your message.', 'contact_form'),
                array( 'status' => 400 )
            );
        } elseif (mb_strlen($message)>500) {
            return new WP_Error('long_message', __('Message is too long.', 'contact_form'), array( 'status' => 400 ));
        }

        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');
        $currentDateTime = date_i18n($dateFormat.' '.$timeFormat, time());

        $messageContent = [
            'name'      => sanitize_text_field($name),
            'email'     => sanitize_email($email),
            'message'   => sanitize_textarea_field($message),
        ];

        $newMessage = wp_insert_post(array(
            'post_title'    => $messageContent['name'] . ' - ' . $currentDateTime,
            'post_type'     => 'cf_message',
            'post_content'  => json_encode($messageContent),
            'post_status'   => 'publish',
            'post_author'   => 1,
        ));

        if (is_wp_error($newMessage)) {
            return new WP_Error(
                'unknown_error',
                __('Oh, Something went wrong!', 'contact_form'),
                array( 'status' => 409 )
            );
        }

        return new WP_Error('success', __('Message successfully sent.', 'contact_form'), array( 'status' => 200 ));
    }
}
