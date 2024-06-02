<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $notif_id = $_POST['notif_id'];
    $reply_message = $_POST['reply_message'];

    $servername = "pembodatabase.mysql.database.azure.com";
    $username = "pemboweb";
    $password = 'Pa$$wordDINS';
    $dbname = "pembodb";
    // Create connection
    $connection = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }

    // Insert user reply into a new table or update existing notifications table
    $sql = "INSERT INTO replies (notif_id, user_id, reply_message) VALUES (?, ?, ?)";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("iis", $notif_id, $user_id, $reply_message);
    $stmt->execute();

    // Retrieve the original helpdesk form details including the ticket number
    $orig_helpdesk_sql = "SELECT * FROM help_desk_forms WHERE id = (SELECT help_desk_form_id FROM notifications WHERE id = ?)";
    $orig_helpdesk_stmt = $connection->prepare($orig_helpdesk_sql);
    $orig_helpdesk_stmt->bind_param("i", $notif_id);
    $orig_helpdesk_stmt->execute();
    $orig_helpdesk_result = $orig_helpdesk_stmt->get_result();
    $orig_helpdesk_row = $orig_helpdesk_result->fetch_assoc();
    $ticket_number = $orig_helpdesk_row['ticket_number'];

    // Insert a new helpdesk form with the reply message linked to the original ticket number
    $new_helpdesk_sql = "INSERT INTO help_desk_forms (name, email, purpose, other_purpose, schedule, message, admin_reply, priority, status, created_at, valid_id, ticket_number)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $new_helpdesk_stmt = $connection->prepare($new_helpdesk_sql);
    $new_helpdesk_stmt->bind_param("ssssssssssss", $orig_helpdesk_row['name'], $orig_helpdesk_row['email'], $orig_helpdesk_row['purpose'], $orig_helpdesk_row['other_purpose'], $orig_helpdesk_row['schedule'], $orig_helpdesk_row['message'], $reply_message, $orig_helpdesk_row['priority'], 'Open', date('Y-m-d H:i:s'), $orig_helpdesk_row['valid_id'], $ticket_number);
    $new_helpdesk_stmt->execute();

    header("location: notification.php");
    exit;
}
?>
