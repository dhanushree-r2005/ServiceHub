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

// Fetch worker ID and request ID
$worker_id = $_SESSION['userid'];
$request_id = $_POST['request_id'];

// Fetch details for the task
$sql = "SELECT sr.Mobile, sr.Address, sr.ServiceID 
        FROM service_request sr 
        JOIN task t ON sr.RequestID = t.RequestID 
        WHERE t.RequestID = ? AND t.WorkerID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL prepare error: " . $conn->error); // Display SQL error
}

$stmt->bind_param('ii', $request_id, $worker_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $task = $result->fetch_assoc();

    // Move the task to the completed_task table
    $insert_sql = "INSERT INTO completed_task (RequestID, WorkerID, CustomerMobile, Address, ServiceID, CompletionDate) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param('iissi', $request_id, $worker_id, $task['Mobile'], $task['Address'], $task['ServiceID']);

    if ($insert_stmt->execute()) {
        // Remove the task from the task table
        $delete_sql = "DELETE FROM task WHERE RequestID = ? AND WorkerID = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param('ii', $request_id, $worker_id);
        $delete_stmt->execute();
        
        echo "Task marked as completed.";
    } else {
        echo "Error completing the task.";
    }

    $insert_stmt->close();
    $delete_stmt->close();
} else {
    echo "No matching task found.";
}

$stmt->close();
$conn->close();
?>
