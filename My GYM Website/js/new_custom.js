$(document).ready(function() {
    // Membership form submission
    $('.membership-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Form submitted'); // Debug log

        // Validate checkbox
        if (!$('#signup-agree').is(':checked')) {
            alert('Please accept the Terms & Conditions');
            return;
        }

        const form = $(this);
        const submitButton = form.find('#submit-button');
        const originalButtonText = submitButton.text();

        // Disable submit button and show loading state
        submitButton.prop('disabled', true).text('Sending...');

        // Log form data for debugging
        console.log('Form data:', form.serialize());

        $.ajax({
            url: 'php/admin/save_enquiry.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                console.log('Response:', response); // Debug log
                if (response.status === 'success') {
                    alert('Thank you for your enquiry! We will contact you soon.');
                    form[0].reset();
                    $('#membershipForm').modal('hide');
                } else {
                    alert(response.message || 'Error submitting enquiry. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                try {
                    const response = JSON.parse(xhr.responseText);
                    alert(response.message || 'Error submitting enquiry. Please try again.');
                } catch (e) {
                    alert('Error submitting enquiry. Please try again.');
                }
            },
            complete: function() {
                // Re-enable submit button and restore text
                submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });

    // Contact form submission
    $('.contact-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Contact form submitted'); // Debug log

        const form = $(this);
        const submitButton = form.find('#submit-button');
        const originalButtonText = submitButton.text();

        submitButton.prop('disabled', true).text('Sending...');

        $.ajax({
            url: 'php/admin/save_enquiry.php',
            method: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                console.log('Contact form response:', response); // Debug log
                if (response.status === 'success') {
                    alert('Thank you for your message! We will get back to you soon.');
                    form[0].reset();
                } else {
                    alert(response.message || 'Error sending message. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                console.error('Contact form error:', {
                    status: status,
                    error: error,
                    response: xhr.responseText
                });
                alert('Error sending message. Please try again.');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(originalButtonText);
            }
        });
    });

    // Phone number formatting
    $('input[name="cf-phone"]').on('input', function() {
        let value = $(this).val().replace(/\D/g, '').substring(0,10);
        if (value.length >= 6) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
        } else if (value.length >= 3) {
            value = value.replace(/(\d{3})(\d{0,3})/, "$1-$2");
        }
        $(this).val(value);
    });

    // Email validation
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    // Form validation
    function validateForm(form) {
        const email = form.find('input[name="cf-email"]').val();
        const name = form.find('input[name="cf-name"]').val();
        
        if (!name) {
            alert('Please enter your name');
            return false;
        }
        
        if (!isValidEmail(email)) {
            alert('Please enter a valid email address');
            return false;
        }
        
        return true;
    }

    // Add form validation before submission
    $('.membership-form, .contact-form').on('submit', function(e) {
        if (!validateForm($(this))) {
            e.preventDefault();
            return false;
        }
    });
});
