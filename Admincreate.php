<?php
// Include database connection
include 'db.php';

// Function to hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $name = htmlspecialchars(trim($_POST["name"]));
    $email = htmlspecialchars(trim($_POST["email"]));
    $password = $_POST["password"];
    
    // Hash the password
    $hashed_password = hashPassword($password);

    // Insert admin into the admins table
    $insert_stmt = $conn->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $name, $email, $hashed_password);
    
    if ($insert_stmt->execute()) {
        echo '<script>alert("Admin created successfully.");</script>';
    } else {
        echo '<script>alert("Error: ' . $insert_stmt->error . '");</script>';
    }

    // Close the statement
    $insert_stmt->close();
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container my-5">
        <h2>Create Admin</h2>
        <form method="post">
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Name</label>
                <div class="col-sm-6">
                    <input type="text" class="form-control" name="name" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Email</label>
                <div class="col-sm-6">
                    <input type="email" class="form-control" name="email" required>
                </div>
            </div>
            <div class="row mb-3">
                <label class="col-sm-3 col-form-label">Password</label>
                <div class="col-sm-6">
                    <input type="password" class="form-control" name="password" required>
                </div>
            </div>
            <div class="row mb-3">
                <div class="offset-sm-3 col-sm-6 d-grid">
                    <button type="submit" class="btn btn-primary">Create Admin</button>
                </div>
            </div>
        </form>
    </div>
</body>
</html>
