<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Signup.css">
    <title>Admin Login</title>
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
                <h2>Admin Login</h2>
                <form id="loginForm" method="post" action="AdminLogin.php">
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="password" name="password" placeholder="Password" required>
                    <button type="submit" class="login-button">Login</button>
                </form>
            </div>
        </div>
    </div>

    <?php
    // Include database connection
    include 'db.php';
    session_start(); // Ensure session is started

    // Define the password expiration period (e.g., 90 days)
    define('PASSWORD_EXPIRATION_DAYS', 90);
    $max_attempts = 3; // Maximum number of failed attempts
    $lockout_time = 1; // Lockout time in minutes

    // Check if the form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Retrieve form data
        $email = htmlspecialchars(trim($_POST["email"]));
        $password = $_POST["password"];

        // Check if the user is locked out
        $lockout_check_stmt = $conn->prepare("SELECT COUNT(*) AS attempt_count FROM login_attempts WHERE email = ? AND attempt_time > NOW() - INTERVAL ? MINUTE");
        $lockout_check_stmt->bind_param("si", $email, $lockout_time);
        $lockout_check_stmt->execute();
        $lockout_result = $lockout_check_stmt->get_result();
        $attempt_data = $lockout_result->fetch_assoc();

        if ($attempt_data['attempt_count'] >= $max_attempts) {
            // User is locked out
            echo '<script>alert("Too many failed login attempts. Please try again later.");</script>';
        } else {
            // Check if the email exists in the 'admins' table
            $check_stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            $result = $check_stmt->get_result();

            if ($result->num_rows == 1) {
                // Email exists, verify password
                $admin = $result->fetch_assoc();
                if (password_verify($password, $admin['password'])) {
                    // Password is correct, check if it has expired
                    $password_expires_at = new DateTime($admin['password_expires_at']);
                    $now = new DateTime();

                    if ($now > $password_expires_at) {
                        // Password has expired, set session variables before redirect
                        $_SESSION['email'] = $email; // Store email in session
                        $_SESSION['admin_id'] = $admin['id']; // Assuming 'id' is the admin ID column in the 'admins' table

                        echo '<script>alert("Your password has expired. Please change your password."); window.location.href="AdminChangepass.php";</script>';
                    } else {
                        // Password is valid and not expired, login successful
                        $_SESSION['email'] = $email; // Store email in session
                        $_SESSION['admin_id'] = $admin['id']; // Assuming 'id' is the admin ID column in the 'admins' table

                        // Log the login action into adminlogs table
                        $admin_id = $admin['id']; // Assuming 'id' is the primary key of the admins table
                        $log_description = "Admin logged in with email: $email";
                        $insert_log_stmt = $conn->prepare("INSERT INTO adminlogs (admin_id, log_type, log_description) VALUES (?, 'Admin Login', ?)");
                        $insert_log_stmt->bind_param("is", $admin_id, $log_description);
                        $insert_log_stmt->execute();
                        $insert_log_stmt->close();

                        // Clear any existing login attempts
                        $clear_attempts_stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
                        $clear_attempts_stmt->bind_param("s", $email);
                        $clear_attempts_stmt->execute();
                        $clear_attempts_stmt->close();

                        // Redirect to a dashboard or home page
                        header("Location: AdminHelpdesk.php");
                        exit();
                    }
                } else {
                    // Password is incorrect
                    $insert_attempt_stmt = $conn->prepare("INSERT INTO login_attempts (email) VALUES (?)");
                    $insert_attempt_stmt->bind_param("s", $email);
                    $insert_attempt_stmt->execute();
                    $insert_attempt_stmt->close();
                    echo '<script>alert("Incorrect password. Please try again.");</script>';
                }
            } else {
                // Email does not exist
                echo '<script>alert("Email not found. Please register.");</script>';
            }

            // Close check statement
            $check_stmt->close();
        }

        // Close the database connection
        $conn->close();
    }
    ?>

    <script src="Login.js"></script>
</body>
</html>
