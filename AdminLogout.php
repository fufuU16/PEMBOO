<?php
// Include database connection
include 'db.php';

// Start session
session_start();

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
