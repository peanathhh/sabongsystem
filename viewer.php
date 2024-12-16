<?php
// Include database connection
include 'cockfight_management.php';

// Fetch ongoing matches (those without end_time or winner) for display
$matches = [];
if (isset($conn)) {
    // Fetch all ongoing matches (those with start_time but no end_time or winner)
    $result = $conn->query("SELECT * FROM matches ORDER BY start_time DESC");
    if ($result) {
        $matches = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "Error fetching matches: " . $conn->error;
    }
} else {
    die("Database connection not established. Please check cockfight_management.php.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viewer - Cockfight Matchups</title>
    <link rel="stylesheet" href="viewer.css">
    <style>
        .match-timer {
            font-size: 1.2em;
            color: #FF5733;
        }

        .match {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .match p {
            font-size: 1.1em;
        }

        .outcome {
            font-size: 1.2em;
            color: #28a745; /* Green for winner */
        }

        .outcome.pending {
            color: #ffc107; /* Yellow for ongoing matches */
        }

        /* Timer Styles */
        .timer-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .timer {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Cockfight Matchup Viewer</h1>
        </header>

        <!-- Ongoing Matches Section -->
        <section class="status-display">
            <h2>Ongoing and Completed Matches</h2>
            <?php if (!empty($matches)): ?>
                <?php foreach ($matches as $row): ?>
                    <div class="match">
                        <p><strong>Match ID:</strong> <?php echo htmlspecialchars($row['id']); ?></p>
                        <p><strong>Meron:</strong> <?php echo htmlspecialchars($row['meron']); ?> vs <strong>Wala:</strong> <?php echo htmlspecialchars($row['wala']); ?></p>
                        <div class="timer-wrapper">
                            <div class="timer">
                                <strong>Time Elapsed:</strong> 
                                <span id="timer-<?php echo $row['id']; ?>">Loading...</span>
                            </div>
                            <div class="outcome <?php echo $row['winner'] ? '' : 'pending'; ?>">
                                <?php 
                                    echo $row['winner'] ? "Winner: " . htmlspecialchars($row['winner']) : "Match Ongoing";
                                ?>
                            </div>
                        </div>
                        <?php if (!empty($row['end_time'])): ?>
                            <p><strong>End Time:</strong> <?php echo htmlspecialchars($row['end_time']); ?></p>
                            <p><strong>Duration:</strong> <?php 
                                // Calculate the time difference between start and end time
                                $start_time = strtotime($row['start_time']);
                                $end_time = strtotime($row['end_time']);
                                $duration = $end_time - $start_time;
                                echo gmdate("H:i:s", $duration); // Format the time in H:i:s
                            ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No ongoing or completed matches.</p>
            <?php endif; ?>
        </section>
    </div>

    <script>
    // Function to update timers for ongoing matches
    function updateTimers() {
        const matches = <?php echo json_encode($matches); ?>; // Fetch match data from PHP

        matches.forEach(function(match) {
            if (!match.end_time) { // Only process ongoing matches
                const startTime = new Date(match.start_time).getTime(); // Match start time
                const currentTime = new Date().getTime(); // Current time
                let elapsedTime = Math.floor((currentTime - startTime) / 1000); // Elapsed time in seconds

                // Format elapsed time as HH:MM:SS
                const hours = Math.floor(elapsedTime / 3600);
                const minutes = Math.floor((elapsedTime % 3600) / 60);
                const seconds = elapsedTime % 60;

                const formattedTime = 
                    (hours < 10 ? "0" : "") + hours + ":" +
                    (minutes < 10 ? "0" : "") + minutes + ":" +
                    (seconds < 10 ? "0" : "") + seconds;

                // Update the timer element
                const timerElement = document.getElementById(`timer-${match.id}`);
                if (timerElement) {
                    timerElement.textContent = formattedTime;
                }
            }
        });
    }

    // Update timers every second
    setInterval(updateTimers, 1000);

    // Run immediately on page load
    window.onload = updateTimers;
</script>


</body>
</html>
