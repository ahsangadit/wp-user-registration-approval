<?php

class User_Registration {

    public function __construct() {
        add_action('user_register', [$this, 'set_user_to_pending']);
        add_shortcode('user_registration_form', [$this, 'render_registration_form']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
    }

     /**
     * Enqueues custom styles and scripts required for the user registration process.
     * This includes jQuery, custom CSS for the registration form, and custom JavaScript 
     * for handling form submissions and AJAX interactions.
     */
    public function enqueue_styles() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('user-approval-style', plugin_dir_url(__FILE__) . '../assets/css/user-approval-style.css');
        wp_enqueue_script('user-approval-script', plugin_dir_url(__FILE__) . '../assets/js/user-approval.js', ['jquery'], '1.0', true);
        wp_localize_script('user-approval-script', 'userApproval', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('user_registration_nonce')
        ]);
    }

    /**
     * Method to set the registered user's status to 'pending' and assign them the 'subscriber' role.
     * This prevents the user from logging in until an admin approves their registration.
     *
     * @param int $user_id The ID of the user who has just registered. 
     *  This ID is passed automatically by the 'user_register' action hook.
     */
    public function set_user_to_pending($user_id) {
        update_user_meta($user_id, 'user_status', 'pending');
        wp_update_user(['ID' => $user_id, 'role' => 'subscriber']);
    }

    /**
     * Renders the user registration form using a shortcode.
     *
     * This method checks if the user is already logged in. If they are, 
     * it returns a message indicating that they are already registered and logged in.
     * If the user is not logged in, it generates the HTML form for user registration.
     *
     * @return string The HTML output of the registration form or a message if the user is logged in.
     */
    public function render_registration_form() {
        if (is_user_logged_in()) {
            return __('You are already registered and logged in.');
        }

        ob_start();
        ?>
        <form id="user-registration-form">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="phone_number">Phone Number:</label>
            <input type="text" id="phone_number" name="phone_number" required><br>

            <!-- Nonce field for security -->
            <?php wp_nonce_field('user_registration_nonce', 'user_registration_nonce_field'); ?>
            <button type="submit">Register</button>
        </form>
        <div id="registration-result"></div>
        <?php
        return ob_get_clean();
    }
}
