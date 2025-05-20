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

// Fetch counts for completed and pending tasks
$completed_tasks_query = "SELECT COUNT(*) AS completed_count FROM completed_task";
$completed_result = $conn->query($completed_tasks_query);
$completed_count = $completed_result->fetch_assoc()['completed_count'];

$pending_tasks_query = "SELECT COUNT(*) AS pending_count FROM task";
$pending_result = $conn->query($pending_tasks_query);
$pending_count = $pending_result->fetch_assoc()['pending_count'];

// Fetch worker list for deletion
$workers_query = "SELECT * FROM workers";
$workers_result = $conn->query($workers_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap');

        body {
            font-family: 'Orbitron', sans-serif;
            background: #1a1a2e;
            color: #fff;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        .dashboard-container {
            width: 100%;
            max-width: 1200px;
            background: #25274d;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
        }

        h1 {
            color: #4ecca3;
            text-align: center;
        }

        .charts {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .chart-container {
            width: 45%;
            background: #2e2e48;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }

        .chart-wrapper {
            height: 300px; /* Set a fixed height */
        }

        .action-container {
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
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
        <h1>Manager Dashboard</h1>
        
        <!-- Graphs Section -->
        <div class="charts">
            <div class="chart-container">
                <h2>Completed Tasks</h2>
                <div class="chart-wrapper">
                    <canvas id="completedChart"></canvas>
                </div>
            </div>
            <div class="chart-container">
                <h2>Pending Tasks</h2>
                <div class="chart-wrapper">
                    <canvas id="pendingChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Workers Management Section -->
        <div class="action-container">
            <h2>Manage Workers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Worker ID</th>
                        <th>Username</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($worker = $workers_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $worker['UserID']; ?></td>
                            <td><?php echo htmlspecialchars($worker['Username']); ?></td>
                            <td>
                                <form action="delete_worker.php" method="post">
                                    <input type="hidden" name="worker_id" value="<?php echo $worker['UserID']; ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Get the counts for completed and pending tasks from PHP
        const completedCount = <?php echo $completed_count; ?>;
        const pendingCount = <?php echo $pending_count; ?>;

        const completedCtx = document.getElementById('completedChart').getContext('2d');
        const pendingCtx = document.getElementById('pendingChart').getContext('2d');

        // Chart for Completed Tasks
        new Chart(completedCtx, {
            type: 'doughnut',
            data: {
                labels: ['Completed Tasks', 'Remaining'],
                datasets: [{
                    data: [completedCount, 100 - completedCount], // Assuming 100 is the total capacity for demonstration
                    backgroundColor: ['#4ecca3', '#cccccc'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });

        // Chart for Pending Tasks
        new Chart(pendingCtx, {
            type: 'doughnut',
            data: {
                labels: ['Pending Tasks', 'Completed'],
                datasets: [{
                    data: [pendingCount, 100 - pendingCount], // Assuming 100 is the total capacity for demonstration
                    backgroundColor: ['#f39c12', '#cccccc'],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
