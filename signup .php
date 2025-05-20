<?php
session_start();

// Database credentials
$host = 'localhost';
$db = 'servicehub'; // Update with your database name
$user = 'root'; // Update with your database username
$pass = ''; // Update with your database password

// Create a connection
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$username = $_POST['username'];
$password = $_POST['password']; // Plain text password (no hashing)
$role = $_POST['role'];
$email = $_POST['email']; // Get the email from the form
$mobile = $_POST['mobile']; // Get the mobile number from the form

// Prepare SQL statement to insert into login table without hashing the password
$sql = "INSERT INTO login (Username, Password, Role) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}
$stmt->bind_param('sss', $username, $password, $role); // Using plain text password

// Execute the statement
if ($stmt->execute()) {
    // Get the last inserted UserID
    $userID = $conn->insert_id;

    // Prepare SQL statement based on role
    if ($role == 'user') {
        $address = $_POST['address'];
        $sql = "INSERT INTO users (UserID, Username, Email, Mobile, Address) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL prepare error: " . $conn->error);
        }
        $stmt->bind_param('issss', $userID, $username, $email, $mobile, $address);
    } elseif ($role == 'worker') {
        $service_id = $_POST['service_id']; // Get the service ID from the form
        $experience = $_POST['experience']; // Get the worker's experience in years
        $sql = "INSERT INTO workers (UserID, Username, Email, Mobile, ServiceID, ExperienceYears) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            die("SQL prepare error: " . $conn->error);
        }
        $stmt->bind_param('isssii', $userID, $username, $email, $mobile, $service_id, $experience);
    } else {
        echo "Invalid role selected.";
        exit();
    }

    // Execute the statement for users or workers
    if ($stmt->execute()) {
        // Sign up was successful, redirect to login page
        echo "Sign up successful! Redirecting to login page...";
        header("refresh:2; url=login.html"); // Redirect after 2 seconds to login page
        exit();
    } else {
        echo "Error: " . $stmt->error; // Show error if any
    }
} else {
    echo "Error: " . $stmt->error; // Show error if any
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
