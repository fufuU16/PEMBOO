<?php
// Include database connection
include 'db.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();
function handlePhpError($errno, $errstr, $errfile, $errline) {
    echo "<script type='text/javascript'>
            alert('Error: $errstr in $errfile on line $errline');
          </script>";
}

set_error_handler('handlePhpError');

$error_log_file = __DIR__ . '/error_log.txt';

function log_error($message) {
    global $error_log_file;
    error_log($message . "\n", 3, $error_log_file);
}
// Check if the user is logged in
if (isset($_SESSION['id'])) {
    // Retrieve admin ID and email from session
    $admin_id = $_SESSION['id'];
    $email = $_SESSION['email'];

    // Insert log entry for logout into AdminLogs table
    $log_type = "Admin Logout"; // Specify log type
    $log_description = "Admin logged out with email: $email";
    $insert_log_stmt = $conn->prepare("INSERT INTO AdminLogs (admin_id, log_type, log_description) VALUES (?, ?, ?)");
    $insert_log_stmt->bind_param("iss", $admin_id, $log_type, $log_description); // Assuming admin_id is an integer
    $insert_log_stmt->execute();
    $insert_log_stmt->close();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to homepage or any other appropriate page
header("Location: AdminLogin.php");
exit();
?>
