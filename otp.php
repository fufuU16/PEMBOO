<?php
// Include the db.php file to establish a database connection
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: Signup.php");
    exit();
}
if (isset($_POST['digit1']) && isset($_POST['digit2']) && isset($_POST['digit3']) && isset($_POST['digit4']) && isset($_POST['digit5']) && isset($_POST['digit6'])) {
    $enteredOTP = $_POST['digit1'] . $_POST['digit2'] . $_POST['digit3'] . $_POST['digit4'] . $_POST['digit5'] . $_POST['digit6'];
    include 'db.php';
    // Retrieve user details from the database and check OTP
    $email = $_SESSION['email']; // Get email from URL parameter

    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $storedOTP = $row['otp'];

        if ($enteredOTP === $storedOTP) {
            // OTP verification successful
            // Log the registration action
            $user_id = $row['id']; // Assuming 'id' is the primary key of the users table
            $log_description = "New user registered with email: $email";
            $insert_log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, 'User Registration', ?)");
            $insert_log_stmt->bind_param("is", $user_id, $log_description);
            $insert_log_stmt->execute();
            $insert_log_stmt->close();
            
            // Redirect to login page
            header("Location: Login.php");
            exit();
        }else {
            echo '<script>alert("Invalid OTP. Please try again.");</script>';
        }
        
    } else {
        // User not found, redirect back to signup page
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
    <title>OTP Verification</title>
    <style>
        .top-image {
                position: relative; /* Change to relative */
            }

        .top-image img {
                width: 100%;
                display: block;
            }

            body {
    margin: 0;
    height: 100vh;
    display: flex;
    justify-content: center; /* Center horizontally */
    align-items: center; /* Center vertically */
    font-family: Arial, sans-serif;
}

form {
    text-align: center;
    background-color: #fff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    max-width: 400px;
    width: 100%;
    display: flex; /* Use flexbox for the form container */
    flex-direction: column; /* Stack flex items vertically */
    align-items: center; /* Center flex items horizontally */
}


        h2 {
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #333;
        }

        .otp-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .otp-icon {
            width: 60px;
            height: 60px;
            margin-bottom: 10px;
        }

        .otp-input {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .otp-input input[type="text"] {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 1.5em;
            border: 2px solid #ccc;
            border-radius: 8px;
            outline: none;
            font-family: Arial, sans-serif;
        }

        button {
            padding: 12px 24px;
            font-size: 1.5em;
            background-color: #3498db;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>

    <form action="otp.php" method="post">
        <h2>Enter OTP</h2>
        <div class="otp-container">
            <div class="otp-input">
                <input type="text" name="digit1" maxlength="1" oninput="moveToNextOrPrev(this, 'digit2', 'digit1')" required>
                <input type="text" name="digit2" maxlength="1" oninput="moveToNextOrPrev(this, 'digit3', 'digit1')" required>
                <input type="text" name="digit3" maxlength="1" oninput="moveToNextOrPrev(this, 'digit4', 'digit2')" required>
                <input type="text" name="digit4" maxlength="1" oninput="moveToNextOrPrev(this, 'digit5', 'digit3')" required>
                <input type="text" name="digit5" maxlength="1" oninput="moveToNextOrPrev(this, 'digit6', 'digit4')" required>
                <input type="text" name="digit6" maxlength="1" required>
            </div>
        </div>
        <button type="submit">Verify OTP</button>
    </form>

    <script>
        function moveToNextOrPrev(input, nextInputName, prevInputName) {
            if (input.value.length >= input.maxLength) {
                var nextInput = document.getElementsByName(nextInputName)[0];
                if (nextInput) {
                    nextInput.focus();
                }
            } else if (input.value.length === 0) {
                var prevInput = document.getElementsByName(prevInputName)[0];
                if (prevInput) {
                    prevInput.focus();
                }
            }
        }
    </script>
</body>
</html>


