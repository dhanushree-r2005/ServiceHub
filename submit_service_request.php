<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'servicehub';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$service_id = $_POST['service'];
$preferred_date = $_POST['preferred_date'];
$preferred_time = $_POST['preferred_time'];
$address = $_POST['address'];
$location = $_POST['location'];
$mobile = $_POST['mobile'];
$user_id = $_SESSION['userid']; // Assuming user ID is stored in session upon login

// Insert into service_request table
$sql = "INSERT INTO service_request (UserID, ServiceID, PreferredDate, PreferredTime, Address, Location, Mobile) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL prepare error: " . $conn->error);
}

$stmt->bind_param('iisssss', $user_id, $service_id, $preferred_date, $preferred_time, $address, $location, $mobile);

if ($stmt->execute()) {
    $request_id = $stmt->insert_id;

    // Check for available workers for the service and date
    $worker_sql = "SELECT * FROM workers WHERE ServiceID = ? AND UserID NOT IN (
                    SELECT WorkerID FROM task WHERE AssignedDate = ?)";
    $worker_stmt = $conn->prepare($worker_sql);

    if (!$worker_stmt) {
        die("SQL prepare error: " . $conn->error);
    }

    $worker_stmt->bind_param('is', $service_id, $preferred_date);
    $worker_stmt->execute();
    $worker_result = $worker_stmt->get_result();
    
    if ($worker_result->num_rows > 0) {
        // Assign the first available worker
        $worker = $worker_result->fetch_assoc();
        $worker_id = $worker['UserID'];
        
        // Insert into task table
        $task_sql = "INSERT INTO task (RequestID, WorkerID, AssignedDate, AssignedTime) VALUES (?, ?, ?, ?)";
        $task_stmt = $conn->prepare($task_sql);

        if (!$task_stmt) {
            die("SQL prepare error: " . $conn->error);
        }

        $task_stmt->bind_param('iiss', $request_id, $worker_id, $preferred_date, $preferred_time);
        
        if ($task_stmt->execute()) {
            // Update status in service_request table
            $update_sql = "UPDATE service_request SET Status = 'Allocated' WHERE RequestID = ?";
            $update_stmt = $conn->prepare($update_sql);

            if (!$update_stmt) {
                die("SQL prepare error: " . $conn->error);
            }

            $update_stmt->bind_param('i', $request_id);
            $update_stmt->execute();
            
            // Return the request ID to the client
            echo json_encode(['status' => 'success', 'request_id' => $request_id]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error allocating task: ' . $task_stmt->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No available workers for the selected service and date.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error submitting service request: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
