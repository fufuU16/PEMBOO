<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Login.css">
    <link rel="icon" type="image/png" href="picture\icon.png">

    <title>Sign Up</title>
   
</head>
<body>
    <div class="top-image">
        <img src="picture/Imagetop.png" alt="Header Image">
    </div>
    <div class="navbar">
        <a href="index.php" >Home</a>
        <a href="BarangayUp.php">BARANGAY UPDATES</a>
        <a href="Entertainment.php">ENTERTAINMENT</a>
        <a href="Helpdesk.php">HELP DESK</a>
        <a href="Login.php"class="login" >LOGIN</a>
    </div>
   
    <div class="Login">
        <div class="login-container">
            <div class="half-left">
                <div class="centered">

                </div>
            </div>
            <div class="half-right">
                <div class="centered">
                </div>
            </div>
        </div>
        <div class="overlay-container">
        <form class="signup-form" action="signup.php" method="POST">
    <div class="overlay-left">
        <h2>Signup</h2>
        <input type="text" id="name" name="name" placeholder="Name">
        <input type="text" id="surname" name="surname" placeholder="Surname">
        <input type="text" id="address" name="address" placeholder="Address">
        <input type="text" id="age" name="age" placeholder="Age">
        <input type="text" id="gender" name="gender" placeholder="Gender">
        <input type="email" id="email" name="email" placeholder="Email">
        <input type="password" id="password" name="password" placeholder="Password">
       
        
                <button type="submit" class="signup-button">Signup</button>
    </div>
</form>

            
            <div class="overlay-right">
          
            </div>
            <div class="centered">
                 <h2>Do you already have an account?</h2>
            <button id="loginButton" class="login-button">Login</button></div>
        </div>
    </div>


   

    <script src="Signup.js"></script>
    <?php
// Include database connection
include 'db.php';

session_start();
// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Function to generate OTP
function generateOTP($length = 6) {
    $otp = "";
    $digits = "0123456789";
    $otp_length = strlen($digits);

    for ($i = 0; $i < $length; $i++) {
        $otp .= $digits[rand(0, $otp_length - 1)];
    }

    return $otp;
}

// Function to send OTP via email using PHPMailer
function sendOTP($email, $otp) {
    $mail = new PHPMailer(true); // Enable exceptions

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'pembowebsite@gmail.com'; // Your SMTP username (sender email)
        $mail->Password = 'uobgfpbywbyistgt'; // Your SMTP password
        $mail->Port = 587; // Adjust the SMTP port if needed
        $mail->SMTPSecure = 'tls'; // Enable TLS encryption, 'ssl' is also possible

        // Sender and recipient details
        $mail->setFrom('pembowebsite@gmail.com', 'PEMBO Love Ko'); // Replace with sender's email and name
        $mail->addAddress($email); // Use the provided user's email

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your OTP for Verification';
        $mail->Body = 'Your OTP is: ' . $otp;

        // Sending email
        if ($mail->send()) {
            echo 'OTP sent successfully to ' . $email;
        } else {
            echo 'Error sending OTP: ' . $mail->ErrorInfo;
        }
    } catch (Exception $e) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize inputs
    $name = htmlspecialchars(trim($_POST["name"]));
    $surname = htmlspecialchars(trim($_POST["surname"]));
    $address = htmlspecialchars(trim($_POST["address"]));
    $age = intval($_POST["age"]);
    $gender = htmlspecialchars(trim($_POST["gender"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = $_POST["password"];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo '<script>alert("Invalid email format.");</script>';
        exit();
    }

    // Check if the email is already used in the 'users' table
    $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result->num_rows > 0) {
        // Email is already used
        echo '<script>alert("Email is already registered. Please use a different email.");</script>';
    } else {
        // Email is not used, proceed with registration

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

        // Generate OTP
        $otp = generateOTP();

        // Insert user info into the 'users' table
        $insert_user_stmt = $conn->prepare("INSERT INTO users (name, surname, address, age, gender, email, password, otp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $insert_user_stmt->bind_param("sssissss", $name, $surname, $address, $age, $gender, $email, $hashedPassword, $otp);
        
        if ($insert_user_stmt->execute()) {
            // User registration successful, send OTP
            $user_id = $insert_user_stmt->insert_id;

            // Insert password into password history
            $insert_password_stmt = $conn->prepare("INSERT INTO password_history (user_id, password) VALUES (?, ?)");
            $insert_password_stmt->bind_param("is", $user_id, $hashedPassword);
            $insert_password_stmt->execute();
            $insert_password_stmt->close();
            sendOTP($email, $otp);
            $_SESSION['email'] = $email;
            // User registration successful, redirect to otp.php
           echo '<script>
        alert("OTP sent successfully!!!");
        window.location.href = "otp.php?email=' . urlencode($email) . '";
      </script>';
        } else {
            echo '<script>alert("Error: ' . $insert_user_stmt->error . '");</script>';
        }

        // Close user statement
        $insert_user_stmt->close();
    }

    // Close check statement
    $check_stmt->close();
}

// Close the database connection
$conn->close();
?>


<script src="Signup.js"></script>


</body>
</html>
