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
]);

require_once(plugin_dir_path(__FILE__) . 'includes/controllers/RestMessage.php');

add_action('init', array (ContactForm::getInstance(), 'registerPostType'));
add_action('plugins_loaded', array (ContactForm::getInstance(), 'pluginSetup'));
add_action('wp_enqueue_scripts', array(ContactForm::getInstance(), 'enqueueAssets'));
add_action('admin_enqueue_scripts', array(ContactForm::getInstance(), 'enqueueAdminAssets'));
add_action('admin_menu', array(ContactForm::getInstance(), 'registerAdminPages'));
add_action('admin_head', array(ContactForm::getInstance(), 'hideAdminPages'));
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
        
        $this->loadLanguage('contact_form');
    }

    /**
     * Register custom post type
     *
     * @return void
     */
    public function registerPostType()
    {
        $labels = array(
        'name'               => _x('Messages', 'post type general name', 'contact_form'),
        'singular_name'      => _x('Message', 'post type singular name', 'contact_form'),
        'menu_name'          => _x('Messages', 'admin menu', 'contact_form'),
        'name_admin_bar'     => _x('Message', 'add new on admin bar', 'contact_form'),
        'add_new'            => _x('Add New', 'contact form', 'contact_form'),
        'add_new_item'       => __('Add New Message', 'contact_form'),
        'new_item'           => __('New Message', 'contact_form'),
        'edit_item'          => __('Edit Message', 'contact_form'),
        'view_item'          => __('View Message', 'contact_form'),
        'all_items'          => __('All Messages', 'contact_form'),
        'search_items'       => __('Search Messages', 'contact_form'),
        'parent_item_colon'  => __('Parent Messages:', 'contact_form'),
        'not_found'          => __('No messages found.', 'contact_form'),
        'not_found_in_trash' => __('No messages found in Trash.', 'contact_form')
        );

        register_post_type('cf_message', array(
        'labels'                    => $labels,
        'description'               => __('Contact Form Messages', 'contact_form'),
        'public'                    => false,
        'show_ui'                   => true,
        'show_in_menu'              => 'contact_form',
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
        ));

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
            $viewPageUrl = menu_page_url('contact_form_view', false);

            $view = sprintf(
                '<a href="%1$s">%2$s</a>',
                esc_url("{$viewPageUrl}&post={$post->ID}"),
                esc_html__('View', 'contact_form')
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
     * Register admin option pages
     *
     * @return void
     */
    public function registerAdminPages()
    {
        add_menu_page(
            __('Contact Form', 'contact_form'),
            __('Contact Form', 'contact_form'),
            'edit_posts',
            'contact_form',
            '',
            'dashicons-text-page',
            20
        );

        add_submenu_page(
            'contact_form',
            __('View message data', 'contact_form'),
            'View message',
            'edit_posts',
            'contact_form_view',
            array($this, 'renderViewMessagePage')
        );

        add_submenu_page(
            'contact_form',
            __('About Contact Form', 'contact_form'),
            __('About', 'contact_form'),
            'edit_posts',
            'contact_form_about',
            array($this, 'renderAboutPage')
        );
    }

    /**
     * Hide pages in admin panel
     *
     * @return void
     */
    public function hideAdminPages()
    {
        remove_submenu_page('contact_form', 'contact_form_view');
    }

    /**
     * Render about page in admin panel
     *
     * @return void
     */
    public function renderAboutPage()
    {
        echo "Yet another simple contact us plugin.";
    }

    /**
     * Render about page in admin panel
     *
     * @return void
     */
    public function renderViewMessagePage()
    {
        $postId = (int)$_GET['post'] ?? false;
        if ($postId && ($post = get_post($postId))) {
            $name = get_post_meta($post->ID, 'cf_name', true);
            $email = get_post_meta($post->ID, 'cf_email', true);
            require($this->plugin_path . 'includes/templates/message-data.php');
        } else {
            require($this->plugin_path . 'includes/templates/message-not-found.php');
        }
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
