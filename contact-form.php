<?php

namespace contactForm;

/**
 * Contact Form
 *
 * Yet another simple Contact Form plugin.
 *
 * @link              https://github.com/AmirHosseinKarimi/contact-form
 * @since             1.0.0
 * @package           contact-form
 *
 * @wordpress-plugin
 * Plugin Name:       Contact Form
 * Plugin URI:        https://github.com/AmirHosseinKarimi/contact-form
 * Description:       Yet another simple Contact Form plugin.
 * Version:           1.0.0
 * Author:            Amir hossein Hossein Zadeh Karimi
 * Author URI:        https://github.com/AmirHosseinKarimi
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       contact-form
 * Domain Path:       /languages
 */

define('CONTACT_FORM_DATA', [
    'version'   => '1.0.0',
    'domain'    =>  'contact-form',
]);

require_once(plugin_dir_path(__FILE__) . 'includes/controllers/RestMessage.php');

add_action('init', array (ContactForm::getInstance(), 'registerPostType'));
add_action('plugins_loaded', array (ContactForm::getInstance(), 'pluginSetup'));
add_action('wp_enqueue_scripts', array(ContactForm::getInstance(), 'enqueueAssets'));
add_action('admin_enqueue_scripts', array(ContactForm::getInstance(), 'enqueueAdminAssets'));
add_action('rest_api_init', array(\RestMessage::getInstance(), 'registerRoutes'));

add_filter('post_row_actions', array(ContactForm::getInstance(), 'modifyPostRowActions'), 10, 2);

add_shortcode('contact_form', array(ContactForm::getInstance(), 'shortcode'));

class ContactForm
{
    /**
     * Plugin instance.
     *
     * @see getInstance()
     * @type object
     */
    protected static $instance = null;

    /**
     * URL to this plugin's directory.
     *
     * @type string
     */
    public $plugin_url = '';

    /**
     * Path to this plugin's directory.
     *
     * @type string
     */
    public $plugin_path = '';

    /**
     * Constructor. Intentionally left empty and public.
     *
     * @see pluginSetup()
     */
    public function __construct()
    {
    }

    /**
     * Used for regular plugin work.
     *
     * @wp-hook plugins_loaded
     * @return  void
     */
    public function pluginSetup()
    {
        $this->plugin_url    = plugins_url('/', __FILE__);
        $this->plugin_path   = plugin_dir_path(__FILE__);
        
        $this->loadLanguage(CONTACT_FORM_DATA['domain']);
    }

    /**
     * Register custom post type
     *
     * @return void
     */
    public function registerPostType()
    {
        $labels = array(
            'name'               => _x('Messages', 'post type general name', CONTACT_FORM_DATA['domain']),
            'singular_name'      => _x('Message', 'post type singular name', CONTACT_FORM_DATA['domain']),
            'menu_name'          => _x('Messages', 'admin menu', CONTACT_FORM_DATA['domain']),
            'name_admin_bar'     => _x('Message', 'add new on admin bar', CONTACT_FORM_DATA['domain']),
            'add_new'            => _x('Add New', 'contact form', CONTACT_FORM_DATA['domain']),
            'add_new_item'       => __('Add New Message', CONTACT_FORM_DATA['domain']),
            'new_item'           => __('New Message', CONTACT_FORM_DATA['domain']),
            'edit_item'          => __('Edit Message', CONTACT_FORM_DATA['domain']),
            'view_item'          => __('View Message', CONTACT_FORM_DATA['domain']),
            'all_items'          => __('All Messages', CONTACT_FORM_DATA['domain']),
            'search_items'       => __('Search Messages', CONTACT_FORM_DATA['domain']),
            'parent_item_colon'  => __('Parent Messages:', CONTACT_FORM_DATA['domain']),
            'not_found'          => __('No messages found.', CONTACT_FORM_DATA['domain']),
            'not_found_in_trash' => __('No messages found in Trash.', CONTACT_FORM_DATA['domain'])
        );

        $args = array(
            'labels'                    => $labels,
            'description'               => __('Contact Form Messages', CONTACT_FORM_DATA['domain']),
            'public'                    => false,
            'show_ui'                   => true,
            'show_in_menu'              => true,
            'show_in_admin_bar'         => false,
            'rewrite'                   => false,
            'supports'                  => array('title', 'custom-fields'),
            'capability_type'           => 'post',
            'map_meta_cap'              => true,
            'capabilities'              => array(
                'create_posts'          => 'do_not_allow',
                'edit_published_posts'  => 'do_not_allow',
            ),
            'show_in_rest'           => true,
            'rest_base'              => 'cf_message',
            'rest_controller_class'  => 'ContactForm\Controllers\RestMessage',
        );

        register_post_type('cf_message', $args);

        remove_post_type_support('cf_message', 'comments');
    }

    /**
     * Modify contact form messages list actions
     *
     * @param array $actions
     * @param object $post
     * @return array
     */
    public function modifyPostRowActions($actions, $post)
    {
        if ($post->post_type == "cf_message") {
            // TODO: Add view message link
            $view = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url('#'),
                esc_html__('View', CONTACT_FORM_DATA['domain'])
            );
            $actions = [$view] + $actions;

            // TODO: Add Permanent Delete link
        }

        return $actions;
    }
    
    /**
     * Enqueue styles and scripts
     *
     * @return void
     */
    public function enqueueAssets()
    {
        wp_enqueue_style(
            'contact-form-main',
            $this->plugin_url . 'dist/styles/main.css',
            false,
            CONTACT_FORM_DATA['version']
        );
        
        wp_enqueue_script(
            'jquery.form',
            $this->plugin_url . 'dist/libraries/jquery.form.min.js',
            ['jquery'],
            '4.3.0'
        );

        wp_enqueue_script(
            'contact-form-main',
            $this->plugin_url . 'dist/scripts/main.js',
            ['jquery.form'],
            CONTACT_FORM_DATA['version'],
            true
        );
    }
    
    /**
     * Enqueue admin styles and scripts
     *
     * @return void
     */
    public function enqueueAdminAssets()
    {
    }

    /**
     * Handle short code
     *
     * @param array $attrs
     * @return string
     */
    public function shortcode($attrs)
    {
        $attrs = shortcode_atts(array(
            'title' => 'Contact Form',
            'submit_text' => 'Send',
        ), $attrs);

        ob_start();
        require($this->plugin_path . 'includes/templates/form.php');
        return ob_get_clean();
    }

    /**
     * Access class working instance
     *
     * @wp-hook plugins_loaded
     * @return  object of this class
     */
    public static function getInstance()
    {
        null === self::$instance and self::$instance = new self;

        return self::$instance;
    }

    /**
     * Loads translation file.
     *
     * Accessible to other classes to load different language files (admin and
     * front-end for example).
     *
     * @wp-hook init
     * @param   string $domain
     * @return  void
     */
    public function loadLanguage($domain)
    {
        load_plugin_textdomain(
            $domain,
            false,
            $this->plugin_path . '/languages'
        );
    }
}
