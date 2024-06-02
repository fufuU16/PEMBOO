<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Signup.css">
    <title>Forgot Password</title>
    <link rel="icon" type="image/png" href="picture\icon.png">

</head>
<body>
    <div class="top-image">
        <img src="picture/Imagetop.png" alt="Header Image">
    </div>
    <div class="navbar">
        <a href="index.php">Home</a>
        <a href="BarangayUp.php">BARANGAY UPDATES</a>
        <a href="Entertainment.php">ENTERTAINMENT</a>
        <a href="Helpdesk.php">HELP DESK</a>
        <a href="Login.php" class="login">LOGIN</a>
    </div>
   
    <div class="Login">
        <div class="login-container">
            <div class="half-left">
                <div class="centered">
                </div>
            </div>
            <div class="half-right">
                <div class="centered">
                </div>
            </div>
        </div>

        <div class="overlay-container">
            <div class="centered">
                <h2>You don’t have an account?</h2>
                <button class="signup-button" id="Signup-button" style="display: flex; justify-content: center;">Signup</button>
            </div>
            <div class="overlay-left">
            </div>
            <div class="overlay-right">
                <h2>Input Your Email</h2>
                <form id="forgotpassForm" method="POST" action="forgotpass.php">
                    <input type="email" name="email" placeholder="Email" required>
                    <button type="submit" class="send-code">Send Code</button>
                </form>
            </div>
        </div>
    </div>
    <?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'db.php';

    $email = $_POST['email'];

    // Check if the email exists in the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user ID for logging
        $user = $result->fetch_assoc();
        $user_id = $user['id'];

        // Generate a new password
        $newPassword = bin2hex(random_bytes(4)); // 8 characters long
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

        // Update the user's password in the database
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        $update_stmt->bind_param("ss", $hashedPassword, $email);
        $update_stmt->execute();

        // Log the password reset request
        $log_description = "User requested a new password for email: $email";
        $insert_log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, 'Password Reset Request', ?)");
        $insert_log_stmt->bind_param("is", $user_id, $log_description);
        $insert_log_stmt->execute();
        $insert_log_stmt->close();

        // Send the email with the new password
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Your SMTP server
            $mail->SMTPAuth = true;
            $mail->Username = 'pembowebsite@gmail.com'; // Your SMTP username (sender email)
            $mail->Password = 'uobgfpbywbyistgt'; // Your SMTP password
            $mail->Port = 587; // Adjust the SMTP port if needed
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption, 'ssl' is also possible

            // Sender and recipient details
            $mail->setFrom('pembowebsite@gmail.com', 'PEMBO Love Ko'); // Replace with sender's email and name
            $mail->addAddress($email); // Use the provided user's email

            $mail->isHTML(true);
            $mail->Subject = 'Your New Password';
            $mail->Body = 'Your new password is: <b>' . htmlspecialchars($newPassword, ENT_QUOTES, 'UTF-8') . '</b><br>Please log in and change your password immediately.';
            $mail->AltBody = 'Your new password is: ' . htmlspecialchars($newPassword, ENT_QUOTES, 'UTF-8') . '. Please log in and change your password immediately.';

            $mail->send();
            echo '<script>alert("A new password has been sent to your email.");</script>';

        } catch (Exception $e) {
            echo '<script>alert("Message could not be sent. Mailer Error: ' . $mail->ErrorInfo . '");</script>';
        }
    } else {
        echo '<script>alert("No account found with that email address.");</script>';
    }

    // Close prepared statements
    $stmt->close();
    $update_stmt->close();

    // Close database connection
    $conn->close();
}
?>



<script>
    function isValidPassword(password) {
        const passwordRegex = /^(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/;
        return passwordRegex.test(password);
    }

    document.getElementById('forgotpassForm').addEventListener('submit', function(event) {
        const password = document.querySelector('input[name="password"]').value;
        
        // Validate password
        if (!isValidPassword(password)) {
            alert('Password must be at least 8 characters long, contain at least 1 uppercase letter, and at least 1 number.');
            event.preventDefault(); // Prevent form submission
            return;
        }
    });
</script>

   <script src="Login.js"></script>

</body>
</html>
