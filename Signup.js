document.addEventListener("DOMContentLoaded", function() {
    // Get the button element by its id
    const loginButton = document.getElementById('loginButton');
    
    // Add click event listener to the button
    loginButton.addEventListener('click', function() {
        // Redirect to Login.php when the button is clicked
        window.location.href = 'Login.php';
    });
});

 

document.querySelector('.signup-form').addEventListener('submit', function(event) {
    var name = document.getElementById('name').value;
    var surname = document.getElementById('surname').value;
    var address = document.getElementById('address').value;
    var age = document.getElementById('age').value;
    var gender = document.getElementById('gender').value;
    var email = document.getElementById('email').value;
    var password = document.getElementById('password').value;

    // Validate if any field is empty
    if (!name || !surname || !address || !age || !gender || !email || !password) {
        alert('Please fill in all fields.');
        event.preventDefault(); // Prevent form submission
        return;
    }

    // Validate password
    if (!isValidPassword(password)) {
        alert('Password must be at least 8 characters long, contain at least 1 uppercase letter, and at least 1 number.');
        event.preventDefault(); // Prevent form submission
        return;
    }
});

function isValidPassword(password) {
    // Password must be at least 8 characters long
    if (password.length < 8) {
        return false;
    }
    // Password must contain at least 1 uppercase letter
    if (!/[A-Z]/.test(password)) {
        return false;
    }
    // Password must contain at least 1 number
    if (!/\d/.test(password)) {
        return false;
    }
    return true;
}