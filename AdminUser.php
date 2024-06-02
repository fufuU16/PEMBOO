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
                // Check if the user is not logged in
                if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
                    // Redirect the user to the login page or show an error message
                    header("Location: AdminLogin.php"); // Change "login.php" to your login page
                    exit();
                }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="Admin.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Users</title>
    <link rel="icon" type="image/png" href="picture\icon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        /* CSS for search bar */
        #searchInput {
            margin-top: 15px;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ced4da;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h2>User</h2>
        <a class="btn btn-primary" href="AdminUser.php">Users</a>
        <a class="btn btn-primary" href="AdminHelpdesk.php">Help Desk</a>
        <a class="btn btn-primary" href="AdminUserLogs.php">User Logs</a>
        <a class="btn btn-primary" href="AdminLogs.php">Admin Logs</a>
        <a class="btn btn-primary" href="AdminLogout.php">Logout</a>
        
        <!-- Search Bar -->
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search...">
        </div>

        <!-- Sorting Dropdown -->
        <div class="mb-3">
            <select id="sortSelect" class="form-select">
                <option value="id">Sort by ID</option>
                <option value="name">Sort by Name</option>
                <option value="surname">Sort by Surname</option>
                <option value="email">Sort by Email</option>
                <option value="created_at">Sort by Created Date</option>
            </select>
        </div>
        
        <table class="table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>name</th>
                    <th>surname</th>
                    <th>address</th>
                    <th>age</th>
                    <th>gender</th>
                    <th>email</th>
                    <th>password</th>
                    <th>otp</th>
                    <th>created_at</th>
                </tr>
            </thead>
            <tbody id="userTableBody">
                <?php
               
                
                $servername = "pembodatabase.mysql.database.azure.com";
                $username = "pemboweb";
                $password = 'Pa$$wordDINS';
                $dbname = "pembodb";

                // Create connection
                $connection = new mysqli($servername, $username, $password, $database);

                // Check connection
                if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                }

                $sql = "SELECT * FROM users";
                $result = $connection->query($sql);

                if (!$result) {
                    die("Invalid query: " . $connection->error);
                }

                while ($row = $result->fetch_assoc()) {
                    echo "
                    <tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['surname']}</td>
                        <td>{$row['address']}</td>
                        <td>{$row['age']}</td>
                        <td>{$row['gender']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['password']}</td>
                        <td>{$row['otp']}</td>
                        <td>{$row['created_at']}</td>
                    </tr>
                    ";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        // Search Functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#userTableBody tr');
            
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                let match = false;
                cells.forEach(cell => {
                    if (cell.textContent.toLowerCase().includes(filter)) {
                        match = true;
                    }
                });
                if (match) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Sort Functionality
        document.getElementById('sortSelect').addEventListener('change', function() {
            const rowsArray = Array.from(document.querySelectorAll('#userTableBody tr'));
            const sortBy = this.value;

            rowsArray.sort((a, b) => {
                const aText = a.querySelector(`td:nth-child(${getColumnIndex(sortBy)})`).textContent.toLowerCase();
                const bText = b.querySelector(`td:nth-child(${getColumnIndex(sortBy)})`).textContent.toLowerCase();

                if (sortBy === 'id' || sortBy === 'created_at') {
                    return new Date(aText) - new Date(bText);
                }

                return aText.localeCompare(bText);
            });

            const tbody = document.getElementById('userTableBody');
            tbody.innerHTML = '';
            rowsArray.forEach(row => tbody.appendChild(row));
        });

        function getColumnIndex(sortBy) {
            switch(sortBy) {
                case 'id': return 1;
                case 'name': return 2;
                case 'surname': return 3;
                case 'email': return 7;
                case 'created_at': return 10;
                default: return 1;
            }
        }
    </script>
</body>
</html>
