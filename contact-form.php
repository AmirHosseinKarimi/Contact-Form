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
    'version' => '1.0.0',
]);

add_action(
    'plugins_loaded',
    array (ContactForm::getInstance(), 'pluginSetup')
);

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
        
        $this->loadLanguage('contact-form');
        $this->enqueueAssets();

        add_action('wp_enqueue_scripts', array($this, 'enqueueAssets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminAssets'));
        
        add_shortcode('contact_form', array($this, 'shortcode'));
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
            'contact-form-main',
            $this->plugin_url . 'dist/scripts/main.js',
            ['jquery'],
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
     * Access this plugin’s working instance
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
