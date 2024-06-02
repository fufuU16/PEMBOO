
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
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = $_POST['email'];
        $oldPassword = $_POST['oldPassword'];
        $newPassword = $_POST['newPassword'];

        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (password_verify($oldPassword, $user['password'])) {
                $user_id = $user['id'];

                $history_stmt = $conn->prepare("SELECT * FROM password_history WHERE user_id = ? ORDER BY changed_at DESC LIMIT 5");
                $history_stmt->bind_param("i", $user_id);
                $history_stmt->execute();
                $history_result = $history_stmt->get_result();
                $is_reused = false;

                while ($history = $history_result->fetch_assoc()) {
                    if (password_verify($newPassword, $history['password'])) {
                        $is_reused = true;
                        break;
                    }
                }

                if ($is_reused) {
                    echo "<script>alert('The new password cannot be one of the last 5 used passwords.');</script>";
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                    $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                    $update_stmt->bind_param("ss", $hashedPassword, $email);
                    if ($update_stmt->execute()) {
                        $log_stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, log_description) VALUES (?, 'Password Update', ?)");
                        $log_description = "Password updated by user with email $email.";
                        $log_stmt->bind_param("is", $user_id, $log_description);
                        $log_stmt->execute();
                        $log_stmt->close();

                        $history_insert_stmt = $conn->prepare("INSERT INTO password_history (user_id, password) VALUES (?, ?)");
                        $history_insert_stmt->bind_param("is", $user_id, $hashedPassword);
                        $history_insert_stmt->execute();
                        $history_insert_stmt->close();

                        echo "<script>alert('Password updated successfully.'); window.location.href='login.php';</script>";
                    } else {
                        echo "<script>alert('Error updating password: " . $update_stmt->error . "');</script>";
                    }
                }

                $history_stmt->close();
            } else {
                echo "<script>alert('Incorrect old password.');</script>";
            }
        } else {
            echo "<script>alert('No user found with that email address.');</script>";
        }

        $stmt->close();
        $conn->close();
    }
    ?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Signup.css">
    <title>Change Password</title>
    <link rel="icon" type="image/png" href="picture\icon.png">

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
        <a href="index.php">Home</a>
        <a href="BarangayUp.php">BARANGAY UPDATES</a>
        <a href="Entertainment.php">ENTERTAINMENT</a>
        <a href="Helpdesk.php">HELP DESK</a>
        <?php

            if(isset($_SESSION['user_id'])) {
                // User is logged in, display dropdown with Logout and Change Password
                echo '
                <div class="dropdown">
                    <a href="#" class="login">Account</a>
                    <div class="dropdown-content">
                        <a href="changepass.php">Change Password</a>
                        <a href="logout.php">Logout</a>
                    </div>
                </div>';
            } else {
                // User is not logged in, display Login
                echo '<a href="login.php" class="login">Login</a>';
            }
        ?>
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
            <div class="centered">
                <h2></h2>
            </div>
            <div class="overlay-left">
            </div>
            <div class="overlay-right">
                <h2>Change Password</h2>
                <form id="changePasswordForm" method="post" action="changepass.php">
                    <?php
                    if (isset($_SESSION['email'])) {
                        echo '<input type="text" name="email" placeholder="Email" value="' . htmlspecialchars($_SESSION['email']) . '" readonly><br><br>';
                    } else {
                        echo '<script>alert("Session expired. Please log in again."); window.location.href="login.php";</script>';
                    }
                    ?>
                    <input type="password" name="oldPassword" placeholder="Old Password" required><br><br>
                    <input type="password" name="newPassword" placeholder="New Password" required><br><br>
                    <button type="submit" name="submit">Change Password</button>
                </form>
            </div>
        </div>
    </div>


    <script src="Login.js"></script>
</body>
</html>
