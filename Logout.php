<?php
// Include database connection
include 'db.php';

// Start session
session_start();

// Check if the user is logged in
if (isset($_SESSION['email'])) {
    // Retrieve user ID and email from session
    $user_id = $_SESSION['user_id'];
    $email = $_SESSION['email'];

    // Insert log entry for logout
    $log_description = "User logged out with email: $email";
    $insert_log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, 'User Logout', ?)");
    $insert_log_stmt->bind_param("is", $user_id, $log_description);
    $insert_log_stmt->execute();
    $insert_log_stmt->close();
}

// Unset all session variables
$_SESSION = [];

// Destroy the session
session_destroy();

// Redirect to homepage or any other appropriate page
header("Location: index.php");
exit();
?>
