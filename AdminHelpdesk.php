<?php
 session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {
    header("Location: AdminLogin.php");
    exit();
}

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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="Admin.css">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>List of Helpdesk Form</title>
    <link rel="icon" type="image/png" href="picture/icon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        .no-reply {
            background-color: #f0f0f0;
        }
        .has-reply {
            background-color: #ffffff;
        }
        .truncate {
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
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
        <h2>List of User's Helpdesk</h2>
        <a class="btn btn-primary" href="AdminLogout.php">Logout</a>
        <br>
        
        <div class="mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Search...">
        </div>

        <div class="mb-3">
            <select id="sortSelect" class="form-select">
                <option value="id">Sort by ID</option>
                <option value="name">Sort by Name</option>
                <option value="email">Sort by Email</option>
                <option value="created_at">Sort by Created Date</option>
            </select>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>name</th>
                    <th>email</th>
                    <th>purpose</th>
                    <th>other_purpose</th>
                    <th>schedule</th>
                    <th>message</th>
                    <th>status</th>
                    <th>priority</th>
                    <th>created_at</th>
                    <th>valid_id</th>
                    <th>ticket_number</th>
                    <th>admin_reply</th>
                    <th>actions</th>
                </tr>
            </thead>
            <tbody id="helpdeskTableBody">
                <?php
                 $servername = "pembodatabase.mysql.database.azure.com";
                 $username = "pemboweb";
                 $password = 'Pa$$wordDINS';
                 $dbname = "pembodb";
                 
                $connection = new mysqli($servername, $username, $password, $dbname);

                if ($connection->connect_error) {
                    die("Connection failed: " . $connection->connect_error);
                }

                $email = $_SESSION['email'];
                $role_query = $connection->prepare("SELECT roles FROM admins WHERE email = ?");
                $role_query->bind_param("s", $email);
                $role_query->execute();
                $role_result = $role_query->get_result();

                if ($role_result->num_rows == 1) {
                    $admin = $role_result->fetch_assoc();
                    $roles = explode(',', $admin['roles']);

                    if (in_array('Super Admin', $roles)) {
                        echo '<a class="btn btn-primary" href="AdminUser.php">Users</a> ';
                        echo '<a class="btn btn-primary" href="AdminHelpdesk.php">Help Desk</a> ';
                        echo '<a class="btn btn-primary" href="AdminUserLogs.php">User Logs</a> ';
                        echo '<a class="btn btn-primary" href="AdminLogs.php">Admin Logs</a>';
                        
                    } elseif (in_array('Junior Admin', $roles)) {
                        echo '<a class="btn btn-primary" href="AdminHelpdesk.php">Help Desk</a>';
                    }
                } else {
                    echo "Error: Unable to retrieve role information.";
                }
                
                $sql = "SELECT * FROM help_desk_forms";
                $result = $connection->query($sql);

                if (!$result) {
                    die("Invalid query: " . $connection->error);
                }

                while ($row = $result->fetch_assoc()) {
                    $rowClass = empty($row['admin_reply']) ? 'no-reply' : 'has-reply';

                    echo "
                    <tr class='$rowClass'>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['purpose']}</td>
                        <td>{$row['other_purpose']}</td>
                        <td>{$row['schedule']}</td>
                        <td class='truncate'>{$row['message']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['priority']}</td>
                        <td>{$row['created_at']}</td>
                        <td>{$row['valid_id']}</td>
                        <td>{$row['ticket_number']}</td>
                        <td class='truncate'>{$row['admin_reply']}</td>
                        <td>
                            <a class='btn btn-primary btn-sm' href='/PEMBO/AdminHelpdeskEdit.php?id={$row['id']}'>Reply</a>
                        </td>
                    </tr>
                    ";
                }
                ?>
            </tbody>
        </table>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const filter = this.value.toLowerCase();
            const rows = document.querySelectorAll('#helpdeskTableBody tr');
            
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

        document.getElementById('sortSelect').addEventListener('change', function() {
            const rowsArray = Array.from(document.querySelectorAll('#helpdeskTableBody tr'));
            const sortBy = this.value;

            rowsArray.sort((a, b) => {
                const aText = a.querySelector(`td:nth-child(${getColumnIndex(sortBy)})`).textContent.toLowerCase();
                const bText = b.querySelector(`td:nth-child(${getColumnIndex(sortBy)})`).textContent.toLowerCase();

                if (sortBy === 'id' || sortBy === 'created_at') {
                    return new Date(aText) - new Date(bText);
                }

                return aText.localeCompare(bText);
            });

            const tbody = document.getElementById('helpdeskTableBody');
            tbody.innerHTML = '';
            rowsArray.forEach(row => tbody.appendChild(row));
        });

        function getColumnIndex(sortBy) {
            switch(sortBy) {
                case 'id': return 1;
                case 'name': return 2;
                case 'email': return 3;
                case 'created_at': return 10;
                default: return 1;
            }
        }
    </script>
</body>
</html>
