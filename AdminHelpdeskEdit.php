<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

function handlePhpError($errno, $errstr, $errfile, $errline) {
    global $error_log_file;
    $message = "Error: $errstr in $errfile on line $errline";
    error_log($message . "\n", 3, $error_log_file);
    echo "<script type='text/javascript'>alert('$message');</script>";
}

set_error_handler('handlePhpError');

$error_log_file = __DIR__ . '/error_log.txt';

function log_error($message) {
    global $error_log_file;
    error_log($message . "\n", 3, $error_log_file);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure the PHPMailer files exist
if (!file_exists(__DIR__ . '/PHPMailer/src/Exception.php') ||
    !file_exists(__DIR__ . '/PHPMailer/src/PHPMailer.php') ||
    !file_exists(__DIR__ . '/PHPMailer/src/SMTP.php')) {
    die('PHPMailer files not found');
}

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

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

$id = "";
$name = "";
$email = "";
$purpose = "";
$other_purpose = "";
$schedule = "";
$message = "";
$status = "";
$priority = "";
$created_at = "";
$admin_reply = "";
$valid_id = "";
$ticket_number = "";

$errorMessage = "";
$successMessage = "";

// Define the base URL for valid ID images
$valid_id_base_url = 'ValidID/';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!isset($_GET["id"])) {
        header("location: AdminHelpDesk.php");
        exit;
    }
    $id = $_GET["id"];
    $sql = "SELECT * FROM help_desk_forms WHERE id=$id";
    $result = $connection->query($sql);
    $row = $result->fetch_assoc();
    if (!$row) {
        header("location: AdminHelpDesk.php");
        exit;
    }

    $name = $row["name"];
    $email = $row["email"];
    $purpose = $row["purpose"];
    $other_purpose = $row["other_purpose"];
    $schedule = $row["schedule"];
    $message = $row["message"];
    $status = $row["status"];
    $priority = $row["priority"];
    $created_at = $row["created_at"];
    $admin_reply = $row["admin_reply"];
    $valid_id = isset($row["valid_id"]) ? $row["valid_id"] : ""; // Check if the key exists
    $ticket_number = $row["ticket_number"];
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST["id"];
    $name = $_POST["name"];
    $email = $_POST["email"];
    $purpose = $_POST["purpose"];
    $other_purpose = $_POST["other_purpose"];
    $schedule = $_POST["schedule"];
    $message = $_POST["message"];
    $status = $_POST["status"];
    $priority = $_POST["priority"];
    $created_at = $_POST["created_at"];
    $admin_reply = $_POST["admin_reply"];
    $valid_id = isset($_POST["valid_id"]) ? $_POST["valid_id"] : ""; // Include valid ID in POST data if exists
    $ticket_number = $_POST["ticket_number"];

    $sql = "UPDATE help_desk_forms SET
            name='$name',
            email='$email',
            purpose='$purpose',
            other_purpose='$other_purpose',
            schedule='$schedule',
            message='$message',
            status='$status',
            priority='$priority',
            created_at='$created_at',
            admin_reply='$admin_reply'
            WHERE id=$id";

    if ($connection->query($sql) === TRUE) {
        $successMessage = "Record updated successfully";

        // Insert log entry into the logs table
        $user_id_query = $connection->prepare("SELECT id FROM users WHERE email = ?");
        $user_id_query->bind_param("s", $email);
        $user_id_query->execute();
        $result = $user_id_query->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $admin_id = $_SESSION['admin_id']; // Assuming the admin ID is stored in the session
            $log_type = "Help Desk Form Update";
            $log_description = "Help desk form with ID $id updated by admin.";
            $log_stmt = $connection->prepare("INSERT INTO adminlogs (admin_id, log_type, log_description) VALUES (?, ?, ?)");
            $log_stmt->bind_param("iss", $admin_id, $log_type, $log_description);
            $log_stmt->execute();

            // Create a notification for the user
            $notification_message = "Your inquiry with ticket number $ticket_number has been updated.<br> Admin reply: $admin_reply<br>You can also check your email to reply to the admin.";

            $notification_stmt = $connection->prepare("INSERT INTO notifications (user_id, email, message, ticket_number) VALUES (?, ?, ?, ?)");
            $notification_stmt->bind_param("isss", $user['id'], $email, $notification_message, $ticket_number);
            $notification_stmt->execute();

        } else {
            echo '<script>alert("Error: User ID not found.");</script>';
        }

        // Send email reply to user
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pembowebsite@gmail.com';
            $mail->Password = 'uobgfpbywbyistgt';
            $mail->Port = 587;
            $mail->SMTPSecure = 'tls';
            
            $mail->setFrom('pembowebsite@gmail.com', 'PEMBO Love Ko');
            $mail->addAddress($email, $name);
            $mail->addReplyTo('pembowebsite@gmail.com', 'PEMBO Love Ko');
            
            $mail->isHTML(true);
            $mail->Subject = 'Your inquiry has been updated';
            $mail->Body = "Dear $name,<br><br>Your inquiry has been updated. Here is the admin's reply:<br><br>$admin_reply<br><br><strong>Your inquiry details:</strong><br><br>
            <ul>
                <li><strong>Name:</strong> $name</li>
                <li><strong>Email:</strong> $email</li>
                <li><strong>Purpose:</strong> $purpose</li>
                <li><strong>Other Purpose:</strong> $other_purpose</li>
                <li><strong>Schedule:</strong> $schedule</li>
                <li><strong>Message:</strong> $message</li>
                <li><strong>Status:</strong> $status</li>
                <li><strong>Priority:</strong> $priority</li>
                <li><strong>Created At:</strong> $created_at</li>
            </ul>";
            
            $mail->send();
            $successMessage .= " Email reply sent to user.";
            
        } catch (Exception $e) {
            $errorMessage .= " Email reply could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        header("location: AdminHelpDesk.php");
        exit;
    } else {
        $errorMessage = "Error updating record: " . $connection->error;
    }
}

?>


<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Helpdesk Edit</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
        <link rel="icon" type="image/png" href="picture\icon.png">

    </head>
    <body>
        <div class="container my-5">
            <h2>Edit Form</h2>
            <?php if (!empty($errorMessage)): ?>
                <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                    <strong><?php echo $errorMessage; ?></strong>
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Ticket Number</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="ticket_number" value="<?php echo $ticket_number; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Name</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="name" value="<?php echo $name; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Email</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="email" value="<?php echo $email; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Purpose</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="purpose" value="<?php echo $purpose; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Other Purpose</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="other_purpose" value="<?php echo $other_purpose; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Schedule</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="schedule" value="<?php echo $schedule; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Message</label>
                    <div class="col-sm-6">
                        <?php 
                        // Truncate the message to 40 characters and add ellipsis if it's longer
                        $display_message = strlen($message) > 40 ? substr($message, 0, 40) . '...' : $message;
                        ?>
                        <textarea class="form-control" name="message" readonly><?php echo $display_message; ?></textarea>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Created At</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control" name="created_at" value="<?php echo $created_at; ?>" readonly>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Status</label>
                    <div class="col-sm-6">
                        <select class="form-control" name="status">
                            <option value="Open" <?php if ($status == 'Open') echo 'selected'; ?>>Open</option>
                            <option value="Closed" <?php if ($status == 'Closed') echo 'selected'; ?>>Closed</option>
                            <option value="In Progress" <?php if ($status == 'In Progress') echo 'selected'; ?>>In Progress</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Priority</label>
                    <div class="col-sm-6">
                        <select class="form-control" name="priority">
                            <option value="Low" <?php if ($priority == 'Low') echo 'selected'; ?>>Low</option>
                            <option value="Medium" <?php if ($priority == 'Medium') echo 'selected'; ?>>Medium</option>
                            <option value="High" <?php if ($priority == 'High') echo 'selected'; ?>>High</option>
                        </select>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Valid ID</label>
                    <div class="col-sm-6">
                        <?php if (!empty($valid_id)): ?>
                            <img src="<?php echo $valid_id_base_url . $valid_id; ?>" alt="Valid ID" class="img-fluid" />
                        <?php else: ?>
                            <p>No valid ID available.</p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label">Admin Reply</label>
                    <div class="col-sm-6">
                        <textarea class="form-control" name="admin_reply"><?php echo $admin_reply; ?></textarea>
                    </div>
                </div>
                <?php if (!empty($successMessage)): ?>
                    <div class="row mb-3">
                        <div class="offset-sm-3 col-sm-6">
                            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                                <strong><?php echo $successMessage; ?></strong>
                                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="row mb-3">
                    <div class="offset-sm-3 col-sm-3 d-grid">
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    <div class="col-sm-3 d-grid">
                        <a class="btn btn-outline-primary" href="AdminHelpDesk.php" role="button">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </body>
</html>
