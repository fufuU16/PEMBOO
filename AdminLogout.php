<?php
// Database connection parameters
$servername = "pembodatabase.mysql.database.azure.com";
$username = "pemboweb";
$password = 'Pa$$wordDINS';
$dbname = "pembodb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
if (isset($_SESSION['id']) && isset($_SESSION['email'])) {
    // Retrieve admin ID and email from session
    $admin_id = $_SESSION['id'];
    $email = $_SESSION['email'];

    // Insert log entry for logout into AdminLogs table
    $log_type = "Admin Logout"; // Specify log type
    $log_description = "Admin logged out with email: $email";
    $insert_log_stmt = $conn->prepare("INSERT INTO AdminLogs (admin_id, log_type, log_description) VALUES (?, ?, ?)");

    if ($insert_log_stmt) {
        $insert_log_stmt->bind_param("iss", $admin_id, $log_type, $log_description); // Assuming admin_id is an integer
        if ($insert_log_stmt->execute()) {
            echo "Log entry inserted successfully.";
        } else {
            log_error("Error executing statement: " . $insert_log_stmt->error);
            echo "Error executing statement: " . $insert_log_stmt->error;
        }
        $insert_log_stmt->close();
    } else {
        log_error("Error preparing statement: " . $conn->error);
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    log_error("Session variables 'id' or 'email' are not set.");
    echo "Session variables 'id' or 'email' are not set.";
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Close the database connection
$conn->close();

// Redirect to homepage or any other appropriate page
header("Location: AdminLogin.php");
exit();
?>
