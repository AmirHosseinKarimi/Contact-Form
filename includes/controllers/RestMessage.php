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
        if (empty($name) || mb_strlen($name)>40) {
            return new WP_Error(
                'invalid_name',
                __('Please enter your name.', 'contact_form'),
                array( 'status' => 400 )
            );
        }

        $email = $request->get_param('email') ?? '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email)>120) {
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

        $postTitle = wp_strip_all_tags($name) . ' - ' . wp_strip_all_tags($email);

        // Do not need sanitizing
        // @see https://developer.wordpress.org/reference/functions/wp_insert_post/#security
        // @see https://wordpress.stackexchange.com/questions/24436/how-safe-sanitized-is-wp-insert-posts
        $newMessage = wp_insert_post(array(
            'post_title'    => $postTitle,
            'post_type'     => 'cf_message',
            'post_content'  => $message,
            'post_status'   => 'publish',
            'post_author'   => 0,
            'comment_status'=> 'closed',
            'meta_input'    => array(
                'read'      => 0,
                'cf_name'   => $name,
                'cf_email'  => $email,
            ),
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
