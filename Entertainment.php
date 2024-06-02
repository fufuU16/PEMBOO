<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="Entertainment.css">
    <link rel="icon" type="image/png" href="picture\icon.png">

    <title>Entertainment</title>
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
        <a href="Entertainment.php"class="home">ENTERTAINMENT</a>
        <a href="Helpdesk.php">HELP DESK</a>
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
   
    <div class="title">
        <h2>FUTURE EVENTS IN</h2>
    </div>
    <div class="titlee">
        <span class="part1">BARANGAY </span>
        <span class="part2">PEMBO</span>
    </div>
  
    <div class="event-container">
    <div class="event">
            <h3>Barangay Basketball League</h3>
            <p>Status: Coming Soon</p>
            <p>Description: Join us for the annual Barangay Pembo Basketball League! Teams from different zones will compete for the championship. The event will be held at the Barangay Sports Complex. Stay tuned for the schedule and team registration details.</p>
        </div>
        <div class="event">
            <h3>Mini Concert Night</h3>
            <p>Status: Coming Soon</p>
            <p>Description: Enjoy a night of music and entertainment at our Mini Concert Night! Local bands and artists will perform live at the Barangay Hall grounds. Food stalls and merchandise booths will be available. Don't miss out on this exciting event!</p>
        </div>
        <div class="event">
            <h3>Community Clean-Up Drive</h3>
            <p>Status: Coming Soon</p>
            <p>Description: Let's keep our barangay clean and green! Join the Community Clean-Up Drive and help make a difference. Volunteers will gather at the Barangay Hall at 7 AM. Cleaning materials and refreshments will be provided.</p>
        </div>
        <div class="event">
            <h3>Health and Wellness Fair</h3>
            <p>Status: Coming Soon</p>
            <p>Description: Attend the Health and Wellness Fair at Barangay Pembo and learn about various health programs and services. Free health check-ups, fitness classes, and wellness talks will be available. Open to all residents.</p>
        </div>
    <!-- Add more events as needed -->
</div>
   <script src="BarangayUp.js"></script>

</body>
</html>
