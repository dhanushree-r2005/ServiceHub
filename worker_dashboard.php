<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'servicehub';
$user = 'root'; // Update with your database username
$pass = ''; // Update with your database password
$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch worker ID from session
$worker_id = $_SESSION['userid'];

// Fetch tasks assigned to the logged-in worker
$sql = "SELECT t.RequestID, t.AssignedDate, t.AssignedTime, sr.Mobile, sr.Address, s.ServiceName 
        FROM task t 
        JOIN service_request sr ON t.RequestID = sr.RequestID
        JOIN services s ON sr.ServiceID = s.ServiceID
        WHERE t.WorkerID = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL prepare error: " . $conn->error); // Display SQL error
}

$stmt->bind_param('i', $worker_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Worker Dashboard</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

        body {
            font-family: 'Orbitron', sans-serif;
            background: #1a1a2e;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .dashboard-container {
            background: #25274d;
            padding: 30px;
            border-radius: 10px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        h1 {
            color: #4ecca3;
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            border: 1px solid #4ecca3;
            text-align: center;
        }

        th {
            background: #2e2e48;
        }

        button {
            padding: 10px 15px;
            border: none;
            background-color: #4ecca3;
            color: #1a1a2e;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #3bba87;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Worker Dashboard</h1>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Customer Mobile</th>
                        <th>Address</th>
                        <th>Assigned Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($task = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($task['ServiceName']); ?></td>
                            <td><?php echo htmlspecialchars($task['Mobile']); ?></td>
                            <td><?php echo htmlspecialchars($task['Address']); ?></td>
                            <td><?php echo htmlspecialchars($task['AssignedDate']); ?></td>
                            <td>
                                <form action="complete_task.php" method="post">
                                    <input type="hidden" name="request_id" value="<?php echo $task['RequestID']; ?>">
                                    <button type="submit">Mark as Completed</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No tasks assigned yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
