<?php
// Include database connection
include 'cockfight_management.php'; // Ensure this file exists and contains the $conn variable

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the cock names from the form
    $cock1 = $_POST['cock1'];
    $cock2 = $_POST['cock2'];

    // Set the current time as the start time
    $start_time = date('Y-m-d H:i:s');

    // Insert the match into the database
    $stmt = $conn->prepare("INSERT INTO matches (meron, wala, start_time) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $cock1, $cock2, $start_time);
    $stmt->execute();

    // Redirect to the admin page to view the match
    header("Location: Admin.php");
    exit;
}

// After updating the match table with winner and end_time
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['stop_match'])) {
    $match_id = $_POST['match_id'];
    $end_time = date('Y-m-d H:i:s'); // Current timestamp
    $winner = $_POST['winner'];

    // Update the match with winner and end_time
    $stmt = $conn->prepare("UPDATE matches SET end_time = ?, winner = ? WHERE id = ?");
    $stmt->bind_param("ssi", $end_time, $winner, $match_id);

    if ($stmt->execute()) {
        // Record match stats in the match_stats table after updating match result

        // Calculate match duration (in seconds)
        $start_time = date('Y-m-d H:i:s'); // You would get this from the DB (start_time column of the match)
        $duration = strtotime($end_time) - strtotime($start_time); // match duration in seconds

        // Count wins for Meron and Wala
        $meron_wins = ($winner == 'Meron') ? 1 : 0;
        $wala_wins = ($winner == 'Wala') ? 1 : 0;

        // Insert or update match stats
        $stats_stmt = $conn->prepare("
            INSERT INTO match_stats (match_id, total_matches, total_duration, meron_wins, wala_wins, report_date)
            VALUES (?, 1, ?, ?, ?, CURDATE())
            ON DUPLICATE KEY UPDATE
            total_matches = total_matches + 1,
            total_duration = total_duration + ?,
            meron_wins = meron_wins + ?,
            wala_wins = wala_wins + ?
        ");
        $stats_stmt->bind_param("iiiiii", $match_id, $duration, $meron_wins, $wala_wins, $duration, $meron_wins, $wala_wins);

        if ($stats_stmt->execute()) {
            echo "Match ended and winner recorded successfully, stats updated!";
        } else {
            echo "Error inserting match stats: " . $stats_stmt->error;
        }
    } else {
        echo "Error updating match: " . $stmt->error;
    }

    // Redirect to refresh the page
    header("Location: Admin.php");
    exit;
}

?>
