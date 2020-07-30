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
        register_rest_route('contactForm', '/submit', array(
            'methods' => 'GET',
            'callback' => array($this, 'handleFormSubmit'),
        ));
    }

    /**
     * Handle form submit action
     *
     * @param array $data
     * @return mixed
     */
    public function handleFormSubmit($data)
    {
        return new WP_Error('incomplete', 'Incomplete form data', array( 'status' => 400 ));
    }
}
