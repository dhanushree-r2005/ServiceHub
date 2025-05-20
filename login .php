<?php
session_start();

$host = 'localhost';
$db = 'servicehub';
$user = 'root'; // Update with your database username
$pass = ''; // Update with your database password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];
$role = $_POST['role'];

$table = 'login';

if ($role == 'user') {
    $sql = "SELECT * FROM $table WHERE username = ? AND role = 'user'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username); 
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($role == 'worker') {
    $sql = "SELECT * FROM $table WHERE username = ? AND role = 'worker'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
} elseif ($role == 'manager') {
    $sql = "SELECT * FROM $table WHERE username = ? AND role = 'manager'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    die("Invalid role");
}

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify the password directly (plain text comparison)
    if ($password === $user['Password']) {  // Plain text comparison
        $_SESSION['loggedin'] = true;
        $_SESSION['role'] = $role;
        $_SESSION['userid'] = $user['UserID']; // Store UserID in the session for later use

        // Redirect based on the role
        if ($role == 'user') {
            header('Location: user_dashboard.html'); // Redirect to user dashboard
        } elseif ($role == 'worker') {
            header('Location: worker_dashboard.php'); // Redirect to worker dashboard
        } elseif ($role == 'manager') {
            header('Location: manager_dashboard.php'); // Redirect to manager dashboard
        }

        exit(); // End script execution after redirection
    } else {
        echo "Invalid login credentials: Wrong password";
    }
} else {
    echo "Invalid login credentials: User not found";
}

$stmt->close();
$conn->close();
?>
