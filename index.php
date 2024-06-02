<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Homepage.css">
    <link rel="icon" type="image/png" href="picture\icon.png">

    <title>Homepage</title>
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
        <a href="Homepage.php"class="home" >Home</a>
        <a href="BarangayUp.php">BARANGAY UPDATES</a>
        <a href="Entertainment.php">ENTERTAINMENT</a>
        <a href="Helpdesk.php">HELP DESK</a>
        <?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function handlePhpError($errno, $errstr, $errfile, $errline) {
    echo "<script type='text/javascript'>
            alert('Error: $errstr in $errfile on line $errline');
          </script>";
}

set_error_handler('handlePhpError');
                session_start();
$error_log_file = __DIR__ . '/error_log.txt';

function log_error($message) {
    global $error_log_file;
    error_log($message . "\n", 3, $error_log_file);
}
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
    <div class="title">
        <h2>WELCOME TO</h2>
    </div>
    <div class="titlee">
        <span class="part1">BARANGAY </span>
        <span class="part2">PEMBO</span>
    </div>
    <div class="slideshow-container">
   
    <div class="mySlides fade">
            
            <img src="picture/slide1.png" style="width: 840px; height: 490px;">
        </div>

        <div class="mySlides fade">
            <img src="picture/slide2.png" style="width: 840px; height: 490px;">
        </div>

        <div class="mySlides fade">
            <img src="picture/slide3.png" style="width: 840px; height: 490px;">
        </div>
       
    </div>
    <div class="missions">
        <div class="mission">
            <h2>MISSION</h2>
            <p>Barangay Pembo's mission is innovative transformation and global change through adopting modernization and open sourcing, sustainably holistic, morally self-replicating. Highest of good of all solutions founded on comprehensive and modifiable community, models duplicated globally that include sustainable development goals for infrastructure, food, education and arts, peace and order disaster resilience, economics and fulfilled living.</p>
        </div>
        <div class="vision">
            <h2>VISION</h2>
            <p>Barangay Pembo's vision is to be Tourism Hub of Makati City with disciplined and God loving citizens living in sustainable and competitive economy with clean and green environment and disaster-resilient infrastructure led by transparent and accountable public servants.</p>
        </div>
    </div>


    <script src="Homepage.js"></script>
</body>
</html>
