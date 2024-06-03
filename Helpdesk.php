<?php  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include 'db.php';

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

function uploadToGitHub($filePath, $fileName, $repo, $path, $branch, $token) {
    $fileContent = base64_encode(file_get_contents($filePath));
    $data = json_encode([
        "message" => "Upload $fileName",
        "content" => $fileContent,
        "branch" => $branch
    ]);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.github.com/repos/$repo/contents/$path/$fileName");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: token ' . $token,
        'User-Agent: PHP Script'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode == 201; // 201 Created
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Helpdesk.css">
    <link rel="icon" type="image/png" href="picture\icon.png">
    <title>Help Desk</title>
    <style>
    .dropdown {
        position: relative;
        display: inline-block;
        float: right;
    }

    .dropdown-content {
        display: none;
        position: absolute;
        min-width: 160px;
        box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
        z-index: 1;
        right: 0;
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
        <a href="index.php">Home</a>
        <a href="BarangayUp.php">BARANGAY UPDATES</a>
        <a href="Entertainment.php">ENTERTAINMENT</a>
        <a href="Helpdesk.php" class="home">HELP DESK</a>
        <?php
        if (isset($_SESSION['user_id'])) {
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
    <form class="help-desk-form" method="post" action="" enctype="multipart/form-data">
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
            $valid_id_path = 'ValidID/' . $valid_id_filename;
            if (!move_uploaded_file($valid_id_tmp, $valid_id_path)) {
                echo '<script>alert("Failed to move the uploaded file.");</script>';
                exit();
            }

            // Upload the file to GitHub
            $repo = 'fufuU16/PEMBO';
            $path = 'ValidID';
            $branch = 'main';
            $token = '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'; // Replace with your GitHub token

            if (!uploadToGitHub($valid_id_path, $valid_id_filename, $repo, $path, $branch, $token)) {
                echo '<script>alert("Failed to upload the file to GitHub.");</script>';
                exit();
            }
        } else {
            echo '<script>alert("No valid ID uploaded or an error occurred.");</script>';
            exit();
        }

        // Generate a unique ticket number
        $ticket_number = 'HD' . uniqid();

        // Insert form data into the database table
        $admin_reply = 'Pending'; // Set default value
        $insert_stmt = $conn->prepare("INSERT INTO help_desk_forms (name, email, purpose, other_purpose, schedule, message, valid_id, ticket_number, admin_reply) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("sssssssss", $name, $user_email, $purpose, $otherPurpose, $schedule, $message, $valid_id_filename, $ticket_number, $admin_reply);

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
