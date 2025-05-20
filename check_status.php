<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'servicehub';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get request ID from form
$request_id = $_POST['request_id'];

// Prepare SQL query to check task details for the given request ID
$sql = "SELECT t.WorkerID, w.Username, w.Mobile, t.AssignedDate, w.ExperienceYears 
        FROM task t 
        JOIN workers w ON t.WorkerID = w.UserID 
        WHERE t.RequestID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL prepare error: " . $conn->error); // Display SQL error
}

$stmt->bind_param('i', $request_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $task = $result->fetch_assoc();
    echo "Worker Assigned: " . htmlspecialchars($task['Username']) . "<br>" .
         "Worker Mobile: " . htmlspecialchars($task['Mobile']) . "<br>" .
         "Assigned Date: " . htmlspecialchars($task['AssignedDate']) . "<br>" .
         "Experience: " . htmlspecialchars($task['ExperienceYears']) . " years";
} else {
    echo "No worker assigned for this request or invalid request ID.";
}

// Close connection
$stmt->close();
$conn->close();
?>
