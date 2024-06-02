<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

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
// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("location: login.php");
    exit;
}

$email = $_SESSION['email'];

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

// Fetch notifications for the logged-in user based on email
$sql = "SELECT * FROM notifications WHERE email = ? ORDER BY created_at DESC";
$stmt = $connection->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

// Mark notifications as read
$update_sql = "UPDATE notifications SET read_status = 1 WHERE email = ? AND read_status = 0";
$update_stmt = $connection->prepare($update_sql);
$update_stmt->bind_param("s", $email);
$update_stmt->execute();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="icon" type="image/png" href="picture\icon.png">

    <link rel="stylesheet" href="Entertainment.css">

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

        .container {
            align-items: center;
            position: relative;
            margin-top: 90px;
            margin-bottom: 90px;
            width: 600px;
            border: 1px solid #ccc; /* Add border */
            border-radius: 5px; /* Add border radius for rounded corners */
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); /* Add box shadow for depth */
            margin-left: auto; /* Center horizontally */
            margin-right: auto; /* Center horizontally */
            font-family: 'Lato', sans-serif;
            border: 1px solid #000; /* Add a 2px solid border */
            border-radius: 10px; /* Adjust top margin of container */
            padding: 20px;
            overflow: hidden; /* Prevent overflow */
        }

        h2 {
            font-size: 24px; /* Adjust font size of heading */
            color: #333; /* Heading color */
        }

        .list-group {
            margin-top: 20px; /* Adjust top margin of notification list */
        }

        .list-group-item {
            border: 1px solid #ddd; /* Border color */
            border-radius: 5px; /* Rounded corners */
            margin-bottom: 10px; /* Spacing between notification items */
            padding: 15px; /* Padding inside each notification item */
            text-align: left; /* Align text to the left */
            word-wrap: break-word; /* Ensure long words break correctly */
            overflow-wrap: break-word; /* Ensure long words break correctly */
        }

        .list-group-item strong {
            font-weight: bold; /* Make the notification message bold */
        }

        .list-group-item small.text-muted {
            color: #888; /* Color for the timestamp */
        }

        .btn-primary {
            background-color: #007bff; /* Primary button color */
            border-color: #007bff; /* Primary button border color */
            color: #fff; /* Button text color */
        }

        .btn-primary:hover {
            background-color: #0056b3; /* Primary button hover color */
            border-color: #0056b3; /* Primary button border hover color */
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
        <a href="Entertainment.php"class="home">ENTERTAINMENT</a>
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
    <div class="container my-5">
        <h2>Notifications</h2>
        <?php if ($result->num_rows > 0): ?>
            <ul class="list-group">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="list-group-item">
                        <strong><?php echo $row['message']; ?></strong>
                        <br><small class="text-muted"><?php echo $row['created_at']; ?></small>
                        <!-- Reply button -->
                        <?php if (strpos($row['message'], 'Admin reply:') === false): ?>
                            <button type="button" class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#replyModal" data-notif-id="<?php echo $row['id']; ?>">Reply</button>
                        <?php endif; ?>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No new notifications.</p>
        <?php endif; ?>
    </div>

    <!-- Reply Modal -->
   

    
</body>
</html>
