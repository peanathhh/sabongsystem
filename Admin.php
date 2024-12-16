<?php
// Include database connection
include 'cockfight_management.php';

// Fetch ongoing matches (those without end_time or winner) for display
$matches = [];
if (isset($conn)) {
    $result = $conn->query("SELECT * FROM matches WHERE end_time IS NULL ORDER BY start_time DESC");
    if ($result) {
        $matches = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error fetching matches: " . $conn->error;
    }
} else {
    die("Database connection not established. Please check cockfight_management.php.");
}

// Stop Match Action: Update winner and end_time
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stop_match'])) {
    $match_id = $_POST['match_id'];
    $end_time = date('Y-m-d H:i:s'); // Current timestamp
    $winner = $_POST['winner'];

    $stmt = $conn->prepare("UPDATE matches SET end_time = ?, winner = ? WHERE id = ?");
    $stmt->bind_param("ssi", $end_time, $winner, $match_id);

    if ($stmt->execute()) {
        echo "Match ended and winner recorded successfully!";
    } else {
        echo "Error updating match: " . $stmt->error;
    }

    // Redirect to refresh the page
    header("Location: Admin.php");
    exit;
}

// Report Type Handling
$report_type = $_GET['report_type'] ?? 'day';
$report_data = [];

switch ($report_type) {
    case 'day':
        $stmt = $conn->prepare("SELECT CURDATE() AS report_date, 
                                      COUNT(*) AS total_matches, 
                                      SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) AS total_duration,
                                      SUM(CASE WHEN winner = 'Meron' THEN 1 ELSE 0 END) AS meron_wins,
                                      SUM(CASE WHEN winner = 'Wala' THEN 1 ELSE 0 END) AS wala_wins
                               FROM matches
                               WHERE DATE(start_time) = CURDATE()");
        break;
    case 'week':
        $stmt = $conn->prepare("SELECT CONCAT('Week ', WEEK(CURDATE())) AS report_date, 
                                      COUNT(*) AS total_matches, 
                                      SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) AS total_duration,
                                      SUM(CASE WHEN winner = 'Meron' THEN 1 ELSE 0 END) AS meron_wins,
                                      SUM(CASE WHEN winner = 'Wala' THEN 1 ELSE 0 END) AS wala_wins
                               FROM matches
                               WHERE WEEK(start_time) = WEEK(CURDATE()) AND YEAR(start_time) = YEAR(CURDATE())");
        break;
    case 'month':
        $stmt = $conn->prepare("SELECT DATE_FORMAT(CURDATE(), '%Y-%m') AS report_date, 
                                      COUNT(*) AS total_matches, 
                                      SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) AS total_duration,
                                      SUM(CASE WHEN winner = 'Meron' THEN 1 ELSE 0 END) AS meron_wins,
                                      SUM(CASE WHEN winner = 'Wala' THEN 1 ELSE 0 END) AS wala_wins
                               FROM matches
                               WHERE MONTH(start_time) = MONTH(CURDATE()) AND YEAR(start_time) = YEAR(CURDATE())");
        break;
    default:
        $stmt = $conn->prepare("SELECT CURDATE() AS report_date, 
                                      COUNT(*) AS total_matches, 
                                      SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)) AS total_duration,
                                      SUM(CASE WHEN winner = 'Meron' THEN 1 ELSE 0 END) AS meron_wins,
                                      SUM(CASE WHEN winner = 'Wala' THEN 1 ELSE 0 END) AS wala_wins
                               FROM matches
                               WHERE DATE(start_time) = CURDATE()");
}

$stmt->execute();
$report_data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cockfight Management System</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .match-timer {
            font-size: 1.2em;
            color: #FF5733;
        }
        .stop-btn {
            background-color: red;
            color: white;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            width: 400px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .modal button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
            font-size: 1.2em;
        }
        .modal .cancel-btn {
            background-color: #f44336;
        }
        .modal select {
            padding: 10px;
            font-size: 1em;
            margin: 10px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Cockfight Management System</h1>
        </header>

        <!-- Match Entry Section -->
        <section class="match-entry">
            <h2>Add New Match</h2>
            <form action="process.php" method="POST">
                <div class="entry">
                    <div class="meron">
                        <label for="cock1">Meron (Red):</label>
                        <input type="text" id="cock1" name="cock1" placeholder="Enter Meron Cock Name" required>
                    </div>
                    <div class="wala">
                        <label for="cock2">Wala (Blue):</label>
                        <input type="text" id="cock2" name="cock2" placeholder="Enter Wala Cock Name" required>
                    </div>
                </div>
                <button type="submit">Start Match</button>
            </form>
        </section>

        <!-- Ongoing Matches Section -->
        <section class="status-display">
            <h2>Ongoing Matches</h2>
            <?php if (!empty($matches)): ?>
                <?php foreach ($matches as $row): ?>
                    <div class="match">
                        <p><strong>Match ID:</strong> <?php echo htmlspecialchars($row['id']); ?></p>
                        <p><strong>Meron:</strong> <?php echo htmlspecialchars($row['meron']); ?> vs <strong>Wala:</strong> <?php echo htmlspecialchars($row['wala']); ?></p>
                        <p><strong>Start Time:</strong> <?php echo htmlspecialchars($row['start_time']); ?></p>
                        <p><strong>Winner:</strong> <?php echo $row['winner'] ? htmlspecialchars($row['winner']) : "Not decided yet"; ?></p>

                        <!-- Timer for ongoing match -->
                        <div class="match-timer">
                            <p>Time Elapsed: <span id="timer-<?php echo $row['id']; ?>">10:00</span></p>
                        </div>

                        <!-- Stop Match Button -->
                        <?php if (empty($row['end_time'])): ?>
                            <button class="stop-btn" onclick="showModal(<?php echo $row['id']; ?>, '<?php echo $row['meron']; ?>', '<?php echo $row['wala']; ?>')">Stop Match</button>
                        <?php else: ?>
                            <p><strong>Match Ended:</strong> <?php echo htmlspecialchars($row['end_time']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No ongoing matches.</p>
            <?php endif; ?>
        </section>

        <!-- Modal for Announcing Winner -->
        <div id="stopMatchModal" class="modal">
            <div class="modal-content">
                <h2>Announce Winner</h2>
                <p id="winnerMessage"></p>
                <form method="POST" action="Admin.php">
                    <input type="hidden" name="match_id" id="match_id">
                    <label for="winner">Select Winner:</label>
                    <select id="winner" name="winner" required>
                        <option value="">Select Winner</option>
                        <option value="Meron">Meron</option>
                        <option value="Wala">Wala</option>
                    </select>
                    <button type="submit" name="stop_match">Confirm Winner</button>
                    <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                </form>
            </div>
        </div>

        <!-- Reports Section -->
        <section class="report-section">
            <h2>Reports</h2>
            <form method="POST" action="process.php">
                <label for="report_type">Select Report Type:</label>
                <select name="report_type" id="report_type" onchange="this.form.submit()">
                    <option value="day" <?= $report_type === 'day' ? 'selected' : '' ?>>Daily</option>
                    <option value="week" <?= $report_type === 'week' ? 'selected' : '' ?>>Weekly</option>
                    <option value="month" <?= $report_type === 'month' ? 'selected' : '' ?>>Monthly</option>
                </select>
            </form>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Report Date</th>
                        <th>Total Matches</th>
                        <th>Total Duration</th>
                        <th>Meron Wins</th>
                        <th>Wala Wins</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($report_data)): ?>
                        <?php foreach ($report_data as $report): ?>
                            <tr>
                                <td><?= htmlspecialchars($report['report_date']); ?></td>
                                <td><?= htmlspecialchars($report['total_matches']); ?></td>
                                <td><?= htmlspecialchars($report['total_duration']); ?> seconds</td>
                                <td><?= htmlspecialchars($report['meron_wins']); ?></td>
                                <td><?= htmlspecialchars($report['wala_wins']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No reports available for the selected period.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </div>

    <script>
        // Show Modal
        function showModal(matchId, meron, wala) {
            const winnerMessage = `Match between ${meron} and ${wala}. Select the winner:`;
            document.getElementById('winnerMessage').innerHTML = winnerMessage;
            document.getElementById('match_id').value = matchId;
            document.getElementById('stopMatchModal').style.display = 'flex';
        }

        // Close Modal
        function closeModal() {
            document.getElementById('stopMatchModal').style.display = 'none';
        }

        // Initialize timers for ongoing matches
        const matches = <?php echo json_encode($matches); ?>;
        
        function initializeTimers() {
            matches.forEach((match) => {
                const timerElement = document.getElementById(`timer-${match.id}`);
                
                if (!match.end_time) { // Only for ongoing matches
                    const countdownDuration = 600;
                    let remainingTime = countdownDuration;
                    
                    const interval = setInterval(() => {
                        if (remainingTime > 0) {
                            remainingTime--;
                            const minutes = Math.floor(remainingTime / 60);
                            const seconds = remainingTime % 60;
                            timerElement.innerHTML = `${minutes}:${seconds < 10 ? '0' + seconds : seconds}`;
                        } else {
                            clearInterval(interval);
                        }
                    }, 1000);
                }
            });
        }
        
        // Call the timer initialization function
        initializeTimers();
    </script>
</body>
</html>
