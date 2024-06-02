<?php
// Enable error reporting and set custom error handler
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session at the beginning
session_start();

// Include database connection
include 'db.php';

// Define lockout policy
$max_attempts = 3; // Maximum number of failed attempts
$lockout_time = 1; // Lockout period in minutes

// Error log file path
$error_log_file = __DIR__ . '/error_log.txt';

function log_error($message) {
    global $error_log_file;
    error_log($message . "\n", 3, $error_log_file);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Retrieve form data
        $email = htmlspecialchars(trim($_POST["email"]));
        $password = $_POST["password"];

        // Check for failed login attempts within the lockout period
        $lockout_check_stmt = $conn->prepare("SELECT COUNT(*) AS attempt_count FROM login_attempts WHERE email = ? AND attempt_time > NOW() - INTERVAL ? MINUTE");
        $lockout_check_stmt->bind_param("si", $email, $lockout_time);
        if (!$lockout_check_stmt->execute()) {
            throw new Exception('Failed to check lockout status: ' . $lockout_check_stmt->error);
        }
        $lockout_result = $lockout_check_stmt->get_result();
        $attempt_data = $lockout_result->fetch_assoc();
        
        if ($attempt_data['attempt_count'] >= $max_attempts) {
            // User is locked out
            echo '<script>alert("Too many failed login attempts. Please try again later.");</script>';
        } else {
            // Check if the email exists in the 'users' table
            $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $check_stmt->bind_param("s", $email);
            if (!$check_stmt->execute()) {
                throw new Exception('Failed to check user email: ' . $check_stmt->error);
            }
            $result = $check_stmt->get_result();

            if ($result->num_rows == 1) {
                // Email exists, verify password
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    // Password is correct, login successful
                    $_SESSION['email'] = $email; // Store email in session
                    $_SESSION['user_id'] = $user['id']; // Assuming 'id' is the user ID column in the 'users' table
                    
                    // Log the login action
                    $user_id = $user['id']; // Assuming 'id' is the primary key of the users table
                    $log_description = "User logged in with email: $email";
                    $insert_log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, 'User Login', ?)");
                    $insert_log_stmt->bind_param("is", $user_id, $log_description);
                    if (!$insert_log_stmt->execute()) {
                        throw new Exception('Failed to log login action: ' . $insert_log_stmt->error);
                    }
                    $insert_log_stmt->close();

                    // Clear failed attempts after successful login
                    $clear_attempts_stmt = $conn->prepare("DELETE FROM login_attempts WHERE email = ?");
                    $clear_attempts_stmt->bind_param("s", $email);
                    if (!$clear_attempts_stmt->execute()) {
                        throw new Exception('Failed to clear login attempts: ' . $clear_attempts_stmt->error);
                    }
                    $clear_attempts_stmt->close();

                    // Redirect to a dashboard or home page
                    header("Location: index.php");
                    exit();
                } else {
                    // Password is incorrect, log the failed attempt
                    $insert_attempt_stmt = $conn->prepare("INSERT INTO login_attempts (email) VALUES (?)");
                    $insert_attempt_stmt->bind_param("s", $email);
                    if (!$insert_attempt_stmt->execute()) {
                        throw new Exception('Failed to log failed attempt: ' . $insert_attempt_stmt->error);
                    }
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
        // Close lockout check statement
        $lockout_check_stmt->close();
    } catch (Exception $e) {
        // Log any exceptions that occur
        log_error($e->getMessage());
        echo '<script>alert("An error occurred. Please try again later.");</script>';
    }
}

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Signup.css">
    <title>Login</title>
    <link rel="icon" type="image/png" href="picture/icon.png">
</head>
<body>
<button type="button" id="adminLoginBtn" style="position: absolute; top: 10px; left: 10px; opacity: 0; width: 20%;"></button>

<div class="top-image">
    <img src="picture/Imagetop.png" alt="Header Image">
</div>
<div class="navbar">
    <button type="button" id="adminLoginBtn" style="display: none;"></button>
    <a href="index.php">Home</a>
    <a href="BarangayUp.php">BARANGAY UPDATES</a>
    <a href="Entertainment.php">ENTERTAINMENT</a>
    <a href="Helpdesk.php">HELP DESK</a>
    <a href="Login.php" class="login">LOGIN</a>
</div>

<div class="Login">
    <div class="login-container">
        <div class="half-left">
            <div class="centered"></div>
        </div>
        <div class="half-right">
            <div class="centered"></div>
        </div>
    </div>
    <div class="overlay-container">
        <div class="centered">
            <h2>You don’t have an account?</h2>
            <button class="signup-button" id="Signup-button" style="display: flex; justify-content: center;">Signup</button>
        </div>
        <div class="overlay-left"></div>
        <div class="overlay-right">
            <h2>Login</h2>
            <form id="loginForm" method="post" action="">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="submit" class="login-button">Login</button>
                <a href="forgotpass.php" class="forgot-password">Forgot Password?</a>
            </form>
        </div>
    </div>
</div>


<script>
    document.getElementById("adminLoginBtn").onclick = function() {
        window.location.href = "AdminLogin.php";
    };
</script>

<script src="Login.js"></script>

</body>
</html>
