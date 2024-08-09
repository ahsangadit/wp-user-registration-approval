jQuery(document).ready(function($) {

    jQuery('#user-registration-form').submit(function(e) {
        e.preventDefault();

        var formData = {
            action: 'register_new_user',
            username: $('#username').val(),
            email: $('#email').val(),
            password: $('#password').val(),
            phone_number: $('#phone_number').val(),
            nonce: $('input[name="user_registration_nonce_field"]').val()
        };

        $.post(userApproval.ajax_url, formData, function(response) {
            jQuery('#registration-result').html(response.data.message);
        });
        
    });


    jQuery('.approve-user, .reject-user').on('click', function() {
        var userId = $(this).data('user-id');
        var status = $(this).hasClass('approve-user') ? 'approved' : 'rejected';

        $.post(userApproval.ajax_url, {
            action: 'update_user_status',
            user_id: userId,
            status: status,
            nonce: userApproval.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error updating user status.');
            }
        });
    });
});
