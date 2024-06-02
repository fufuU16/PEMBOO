document.addEventListener("DOMContentLoaded", function() {
    // Get the button element by its id
    const SignupButton = document.getElementById('Signup-button');
    
    // Add click event listener to the button
    SignupButton.addEventListener('click', function() {
        // Redirect to Login.php when the button is clicked
        window.location.href = 'Signup.php';
    });
});
