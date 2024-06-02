<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

if (!isset($_SESSION['email'])) {
    echo '<script>alert("Session expired or invalid access. Please log in again."); window.location.href="login.php";</script>';
    exit();
}

if (isset($_POST['digit1'], $_POST['digit2'], $_POST['digit3'], $_POST['digit4'], $_POST['digit5'], $_POST['digit6'])) {
    $enteredOTP = $_POST['digit1'] . $_POST['digit2'] . $_POST['digit3'] . $_POST['digit4'] . $_POST['digit5'] . $_POST['digit6'];
    include 'db.php';

    $email = $_SESSION['email'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $storedOTP = $row['otp'];

        if ($enteredOTP === $storedOTP) {
            $user_id = $row['id'];
            $log_description = "New user registered with email: $email";
            $insert_log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, 'User Registration', ?)");
            $insert_log_stmt->bind_param("is", $user_id, $log_description);
            $insert_log_stmt->execute();
            $insert_log_stmt->close();

            header("Location: Login.php");
            exit();
        } else {
            echo '<script>alert("Invalid OTP. Please try again.");</script>';
        }
    } else {
        header("Location: Signup.php?error=user_not_found");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="otp.css">
    <title>Verify OTP</title>
</head>
<body>
    <form action="otp.php" method="POST">
        <label for="digit1">Enter OTP:</label>
        <input type="text" id="digit1" name="digit1" maxlength="1" required>
        <input type="text" id="digit2" name="digit2" maxlength="1" required>
        <input type="text" id="digit3" name="digit3" maxlength="1" required>
        <input type="text" id="digit4" name="digit4" maxlength="1" required>
        <input type="text" id="digit5" name="digit5" maxlength="1" required>
        <input type="text" id="digit6" name="digit6" maxlength="1" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
