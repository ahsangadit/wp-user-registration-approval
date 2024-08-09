<?php
/**
 * Plugin Name: User Approval System
 * Version: 1.0
 * Author: Ahsan
 * Description: This plugin manages user registrations with an approval process. It provides a shortcode `[user_registration_form]` to render a registration form on the front end. Registered users are initially set to 'pending' status and require admin approval to access the site.
 */

if (!defined('ABSPATH')) {
    exit;
}

class User_Approval_System {

    /**
     * Initializes the plugin by including necessary files and instantiating classes.
     */
    public function __construct() {
        $this->includes();
        new User_Approval();
        new User_Registration();
    }

    /**
     * Includes the required class files for user approval and registration functionality.
     */
    private function includes() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-user-approval.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-user-registration.php';
        require_once plugin_dir_path(__FILE__) . 'includes/user-registration-ajax.php';
    }
}

new User_Approval_System();
