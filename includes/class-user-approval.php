<?php

class User_Approval {

    public function __construct() {
        add_action('manage_users_columns', [$this, 'add_status_column']);
        add_action('manage_users_custom_column', [$this, 'manage_status_column'], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_update_user_status', [$this, 'update_user_status']);
        add_filter('authenticate', [$this, 'restrict_pending_users'], 30, 3);
    }

    /**
     * Adds a 'Status' column to the user list table in the admin panel.
     *
     * @param array $columns Existing columns in the user list table.
     * @return array Modified columns with 'Status' added.
     */
    public function add_status_column($columns) {
        $columns['user_status'] = __('Status');
        return $columns;
    }


    /**
     * Manages the content displayed in the 'Status' column of the user list table.
     *
     * Displays action buttons for pending users and the user's status for others.
     *
     * @param string $value The current value to be displayed in the column.
     * @param string $column_name The name of the column being managed.
     * @param int $user_id The ID of the user.
     * @return string The modified value to be displayed in the 'Status' column.
     */
    public function manage_status_column($value, $column_name, $user_id) {
        if ('user_status' === $column_name) {
            $status = get_user_meta($user_id, 'user_status', true);
            if ($status === 'pending') {
                $value = '<button class="approve-user" data-user-id="' . $user_id . '">Approve</button> 
                        <button class="reject-user" data-user-id="' . $user_id . '">Reject</button>';
            } else {
                $value = ucfirst($status);
            }
        }
        return $value;
    }


    /**
     * Enqueues scripts on the user list page in the admin panel.
     *
     * Loads the custom JavaScript file and localizes script with AJAX URL and nonce for secure requests.
     *
     * @param string $hook_suffix The current admin page hook suffix.
     */
    public function enqueue_scripts($hook_suffix) {
        if ($hook_suffix === 'users.php') {
            wp_enqueue_script('user-approval-script', plugin_dir_url(__FILE__) . '../assets/js/user-approval.js', ['jquery'], '1.0', true);
            wp_localize_script('user-approval-script', 'userApproval', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('update_user_status_nonce')
            ]);
        }
    }

    /**
     * Updates the status of a user via AJAX request.
     *
     * Validates the nonce for security, sanitizes the input data, updates the user's status, 
     * and returns a success message.
     */
    public function update_user_status() {
        check_ajax_referer('update_user_status_nonce', 'nonce');
        $user_id = intval($_POST['user_id']);
        $status = sanitize_text_field($_POST['status']);
        update_user_meta($user_id, 'user_status', $status);
        wp_send_json_success(__('User status updated.'));
    }


    /**
     * Restricts login for users with 'pending' or 'rejected' status.
     *
     * Checks the user's status and returns an error if the status is 'pending' or 'rejected',
     * preventing login and providing appropriate error messages.
     *
     * @param WP_User|WP_Error $user The user object or WP_Error if the user is invalid.
     * @param string $username The username of the user attempting to log in.
     * @param string $password The password of the user attempting to log in.
     * @return WP_User|WP_Error The user object if login is allowed, otherwise a WP_Error.
     */
    public function restrict_pending_users($user, $username, $password) {
        if (isset($user->ID)) {
            $status = get_user_meta($user->ID, 'user_status', true);
            if ($status === 'pending') {
                return new WP_Error('pending_approval', __('Your status is pending approval.'));
            } elseif ($status === 'rejected') {
                return new WP_Error('rejected_approval', __('Your registration request has been denied by the admin.'));
            }
        }
        return $user;
    }

}
