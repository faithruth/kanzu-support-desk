<?php

namespace Kanzu\Muzimbi;

if (!defined('ABSPATH')) {
    exit;
}

class Scripts
{

    private $container;

    public function __construct()
    {
        $this->container = Kanzu_Muzimbi()->container;
    }

    public function init_styles()
    {
        wp_register_style('bootstrap-css', KZ_MUZIMBI_PLUGIN_URL . 'assets/css/bootstrap.min.css');
        wp_register_style('flexslider-css', KZ_MUZIMBI_PLUGIN_URL . 'assets/css/flexslider.css');
        wp_register_style('jquery-ui-css', KZ_MUZIMBI_PLUGIN_URL . 'assets/css/jquery-ui.css');
        wp_enqueue_style(KZ_MUZIMBI_SLUG . '-css', KZ_MUZIMBI_PLUGIN_URL . 'assets/css/kanzu-muzimbi.css', array('bootstrap-css', 'flexslider-css', 'jquery-ui-css'), time());
    }

    public function init_scripts()
    {
        wp_register_script('flexslider-js', KZ_MUZIMBI_PLUGIN_URL . 'assets/js/jquery.flexslider-min.js', array('jquery'), '', true);
        wp_enqueue_script(KZ_MUZIMBI_SLUG . '-js', KZ_MUZIMBI_PLUGIN_URL . 'assets/js/kanzu-muzimbi.js', array('jquery', 'flexslider-js', 'password-strength-meter'), time());
        wp_enqueue_script('jquery-validate', KZ_MUZIMBI_PLUGIN_URL . 'assets/js/jquery.validate.min.js');
        wp_enqueue_script('additional-methods', KZ_MUZIMBI_PLUGIN_URL . 'assets/js/jquery.validate.additional-methods.js', array('jquery-validate'));
        wp_enqueue_script('jquery-ui', KZ_MUZIMBI_PLUGIN_URL . 'assets/js/jquery-ui.js', array('jquery'));
        wp_register_script('muzimbi-customizer-js', KZ_MUZIMBI_PLUGIN_URL . 'assets/js/kanzu-muzimbi-customizer.js', array('jquery'));
        wp_enqueue_script('muzimbi-customizer-js');
        wp_localize_script('muzimbi-customizer-js', 'customize_site', array('admin_url' => admin_url()));

        $this->localize_scripts();
    }

    public function get_script_data()
    {
        $script_data = array(
            'ajaxUrl'                   => admin_url('admin-ajax.php'),
            'labelProceed'              => __( 'Proceed', 'kanzu-muzimbi'),
            'labelSiteNameAlphanumeric' => __( 'Only letters and numbers allowed', 'kanzu-muzimbi'),
            'labelSiteNameRequired'     => __( 'Please enter your site name', 'kanzu-muzimbi'),
            'labelSiteAboutRequired'    => __( 'Please indicate what your site is about', 'kanzu-muzimbi'),
            'labelGoalRequired'         => __( 'Please select the goal of your site', 'kanzu-muzimbi'),

        );

        return $script_data;
    }

    public function localize_scripts()
    {
        wp_localize_script(
            KZ_MUZIMBI_SLUG . '-js', 'kanzuMuzimbi', $this->get_script_data()
        );
    }

    public function render_muzimbi_widgets()
    {
        register_sidebar(array(
            'name'          => 'Muzimbi features',
            'id'            => 'muzimbi-features',
            'description'   => 'Add widgets here to populate muzimbi features section on landing page',
            'before_widget' => '<section id="%1$s" class="widget %2$s col-lg-6 col-md-6 ">',
            'after_widget'  => '</section>',
            'before_title'  => '<h2 class="widget-title">',
            'after_title'   => '</h2>',
        ));
    }

    public function add_google_analytics_snippet()
    {
        ob_start();
        require_once KZ_MUZIMBI_DIR . '/assets/js/kanzu-muzimbi-google-analytics.js';
        echo ob_get_clean();

    }
}
new Scripts();
