<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Helpdesk.css">
    <link rel="icon" type="image/png" href="picture\icon.png">

    <title>Help Desk</title>
    <style>
/* Adjusted CSS for dropdown */
.dropdown {
    position: relative;
    display: inline-block;
    float: right; /* Float the dropdown to the right */
}

.dropdown-content {
    display: none;
    position: absolute;
    min-width: 160px;
    box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
    z-index: 1;
    right: 0; /* Align dropdown content to the right */
}

.dropdown-content a {
    color: black;
    padding: 12px 16px;
    text-decoration: none;
    display: block;
}

.dropdown-content a:hover {
    background-color: #f1f1f1;
}

.dropdown:hover .dropdown-content {
    display: block;
}

    </style>
</head>
<body>
    <div class="top-image">
        <img src="picture/Imagetop.png" alt="Header Image">
    
    </div>
    <div class="navbar">
        <a href="index.php" >Home</a>
        <a href="BarangayUp.php">BARANGAY UPDATES</a>
        <a href="Entertainment.php">ENTERTAINMENT</a>
        <a href="Helpdesk.php"class="home">HELP DESK</a>
        <?php
                session_start();

if(isset($_SESSION['user_id'])) {
    // User is logged in, display dropdown with Logout and Change Password
    echo '
    <div class="dropdown">
    <a href="#" class="login">Account</a>
        <div class="dropdown-content">
            <a href="changepass.php">Change Password</a>
            <a href="notification.php">Notifications</a>

            <a href="logout.php">Logout</a>
        </div>
    </div>';
} else {
    // User is not logged in, display Login
    echo '<a href="login.php" class="login">Login</a>';
}
?>
    </div>
   
  
    <div class="titlee">
        <span class="part1">BARANGAY </span>
        <span class="part2">PEMBO</span>
    </div>
    <div class="title">
        <h4>HELP DESK</h4>
    </div>
                <form class="help-desk-form" method="post" action=""enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="purpose">Purpose:</label>
                    <select id="purpose" name="purpose" required>
                        <option value="" disabled selected>Select purpose</option>
                        <option value="Barangay ID">Barangay ID</option>
                        <option value="Certificates">Certificates</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group" id="other-purpose-group" style="display: none;">
                    <label for="other-purpose">Other Purpose:</label>
                    <input type="text" id="other-purpose" name="other-purpose">
                </div>
                <div class="form-group">
                    <label for="schedule">Target Schedule:</label>
                    <input type="datetime-local" id="schedule" name="schedule" required>
                </div>
                <div class="form-group">
                <label for="valid_id">Upload Valid ID:</label>
                <input type="file" id="valid_id" name="valid_id" accept="image/*" required>
            </div>
                <div class="form-group">
                    <label for="message">Message:</label>
                    <textarea id="message" name="message" rows="4" required></textarea>
                </div>
                <button type="submit">Submit</button>
            </form>
            <?php
// Include database connection
include 'db.php';

// Start session
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    echo '<script>alert("You must be logged in to submit the form.");</script>';
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $user_email = $_SESSION['email']; // User's email address
    $purpose = htmlspecialchars(trim($_POST["purpose"]));
    $otherPurpose = isset($_POST["other-purpose"]) ? htmlspecialchars(trim($_POST["other-purpose"])) : "";
    $schedule = $_POST["schedule"];
    $message = htmlspecialchars(trim($_POST["message"]));

    // File upload handling for valid ID
    $valid_id_filename = '';
    $valid_id_path = '';

    if (isset($_FILES['valid_id']) && $_FILES['valid_id']['error'] == UPLOAD_ERR_OK) {
        // Get the file name and temporary location
        $valid_id_tmp = $_FILES['valid_id']['tmp_name'];
        $valid_id_name = $_FILES['valid_id']['name'];

        // Generate a unique name for the file to prevent overwrite
        $valid_id_filename = uniqid() . '_' . $valid_id_name;
        
        // Move the file to the specified directory
        $valid_id_path = 'C:/xampp/htdocs/PEMBO/ValidID/' . $valid_id_filename;
        if (!move_uploaded_file($valid_id_tmp, $valid_id_path)) {
            echo '<script>alert("Failed to move the uploaded file.");</script>';
            exit();
        }
    } else {
        echo '<script>alert("No valid ID uploaded or an error occurred.");</script>';
        exit();
    }

    // Generate a unique ticket number
    $ticket_number = 'HD' . uniqid();

    // Insert form data into the database table
    $insert_stmt = $conn->prepare("INSERT INTO help_desk_forms (name, email, purpose, other_purpose, schedule, message, valid_id, ticket_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $insert_stmt->bind_param("ssssssss", $name, $user_email, $purpose, $otherPurpose, $schedule, $message, $valid_id_filename, $ticket_number);

    if ($insert_stmt->execute()) {
        // Form data inserted successfully

        // Send email notification to company email
        require 'PHPMailer/src/Exception.php';
        require 'PHPMailer/src/PHPMailer.php';
        require 'PHPMailer/src/SMTP.php';

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'pembowebsite@gmail.com'; // Your email
            $mail->Password = 'uobgfpbywbyistgt'; // Your email password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom($user_email); // Sender is user's email
            $mail->addAddress('pembowebsite@gmail.com'); // Your company email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'New Help Desk Form Submission';
            $mail->Body = "
                <h3>New Help Desk Form Submission</h3>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Email:</strong> $user_email</p>
                <p><strong>Purpose:</strong> $purpose</p>
                <p><strong>Other Purpose:</strong> $otherPurpose</p>
                <p><strong>Schedule:</strong> $schedule</p>
                <p><strong>Message:</strong> $message</p>
                <p><strong>Ticket Number:</strong> $ticket_number</p>
            ";
            $mail->addAttachment($valid_id_path, 'valid_id_image');

            // Send email
            $mail->send();

            // Insert log entry into the logs table
            $user_id_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $user_id_query->bind_param("s", $user_email);
            $user_id_query->execute();
            $result = $user_id_query->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $user_id = $user['id'];
                $log_type = "Help Desk Form Submission";
                $log_description = "User with email $user_email submitted a help desk form.";
                $log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, ?, ?)");
                $log_stmt->bind_param("iss", $user_id, $log_type, $log_description);
                $log_stmt->execute();
                $log_stmt->close();
            } else {
                echo '<script>alert("Error: User ID not found.");</script>';
            }

            // Clear addresses and attachments for the next email
            $mail->clearAddresses();
            $mail->clearAttachments();
            $mail->addAddress($user_email); // Recipient is user's email
            $mail->Subject = 'Thank you for your Help Desk Form Submission';
            $mail->Body = "Dear $name,<br><br>Thank you for submitting the Help Desk Form. We will review your request and get back to you as soon as possible.<br><br>Your Ticket Number is: $ticket_number<br><br>Best regards,<br>Pembo Website";

            // Send email without attachment
            $mail->send();
            echo '<script>alert("Form submitted successfully! We will get back to you soon.");</script>';
        } catch (Exception $e) {
            echo "Mailer Error: {$mail->ErrorInfo}";
        }

        $user_id_query->close();

    } else {
        // Error in inserting form data
        echo '<script>alert("Error: ' . $insert_stmt->error . '");</script>';
    }

    // Close insert statement
    $insert_stmt->close();
}

// Close the database connection
$conn->close();
?>


   <script src="Helpdesk.js"></script>

</body>
</html>
