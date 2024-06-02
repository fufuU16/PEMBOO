<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Signup.css">
    <title>Admin Change Password</title>
    <link rel="icon" type="image/png" href="picture\icon.png">

</head>
<body>
    <div class="top-image">
        <img src="picture/Imagetop.png" alt="Header Image">
    </div>
    <div class="Login">
        <div class="login-container">
            <div class="half-left">
                <div class="centered">
                    <!-- Add content if needed -->
                </div>
            </div>
            <div class="half-right">
                <div class="centered">
                    <!-- Add content if needed -->
                </div>
            </div>
        </div>

        <div class="overlay-container">
            <div class="centered">
                <h2></h2>
            </div>
            <div class="overlay-left">
                <!-- Add content if needed -->
            </div>
            <div class="overlay-right">
                <h2>Change Password</h2>
                <form id="changePasswordForm" method="post" action="AdminChangepass.php">
                    <input type="password" name="new_password" placeholder="New Password" required>
                    <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
                    <button type="submit" class="change-password-button">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Include database connection
    include 'db.php';
    session_start(); // Ensure session is started

    // Ensure PASSWORD_EXPIRATION_DAYS is defined
    define('PASSWORD_EXPIRATION_DAYS', 90);

    // Check if session variables are set
    if (!isset($_SESSION['email']) || !isset($_SESSION['admin_id'])) {
        echo '<script>alert("Session expired. Please log in again."); window.location.href="AdminLogin.php";</script>';
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];
        $email = $_SESSION['email'];
        $admin_id = $_SESSION['admin_id'];

        // Check if new password and confirm password match
        if ($new_password === $confirm_password) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

            // Set the password expiration date
            $password_expires_at = (new DateTime())->add(new DateInterval('P' . PASSWORD_EXPIRATION_DAYS . 'D'))->format('Y-m-d H:i:s');

            // Update the password in the database
            $update_stmt = $conn->prepare("UPDATE admins SET password = ?, password_expires_at = ? WHERE email = ?");
            $update_stmt->bind_param("sss", $hashed_password, $password_expires_at, $email);
            if ($update_stmt->execute()) {
                // Log the password change action into adminlogs table
                $log_description = "Admin changed password for email: $email";
                $insert_log_stmt = $conn->prepare("INSERT INTO adminlogs (admin_id, log_type, log_description) VALUES (?, 'Password Change', ?)");
                $insert_log_stmt->bind_param("is", $admin_id, $log_description);
                $insert_log_stmt->execute();
                $insert_log_stmt->close();

                echo '<script>alert("Password changed successfully."); window.location.href="AdminLogin.php";</script>';
            } else {
                echo '<script>alert("Failed to change the password. Please try again.");</script>';
            }

            $update_stmt->close();
        } else {
            echo '<script>alert("Passwords do not match. Please try again.");</script>';
        }

        // Close the database connection
        $conn->close();
    }
    ?>

    <script src="Login.js"></script>
</body>
</html>
