<?php

/**
 * Handles the AJAX user registration process.
 *
 * This function processes the registration form submission, verifying the nonce for security,
 * sanitizing the input data, checking for existing users, creating a new user account,
 * and updating user meta data with the phone number and pending status.
 * It responds with a JSON message indicating success or failure.
 *
 * @return void
 */

add_action('wp_ajax_nopriv_register_new_user', 'handle_user_registration');
add_action('wp_ajax_register_new_user', 'handle_user_registration');

function handle_user_registration() {
    // Verify the nonce to ensure the request is secure.
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'user_registration_nonce')) {
        wp_send_json_error(['message' => 'Nonce verification failed.']);
    }

    // Sanitize the input data from the form.
    $username = sanitize_user($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $phone_number = sanitize_text_field($_POST['phone_number']);

    // Check if the username or email already exists in the database.
    if (username_exists($username) || email_exists($email)) {
        wp_send_json_error(['message' => 'Username or email already exists.']);
    }

    // Create a new user with the sanitized username, password, and email.
    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    // Update the user meta with the phone number and set the user's status to 'pending'.
    update_user_meta($user_id, 'phone_number', $phone_number);
    update_user_meta($user_id, 'user_status', 'pending');

    // Send a success response with a message indicating the registration is pending approval.
    wp_send_json_success(['message' => 'Registration successful! Your account is pending approval.']);
}
