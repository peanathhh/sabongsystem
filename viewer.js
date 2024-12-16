// Get the match start time from PHP (convert to a timestamp)
const startTime = new Date("<?php echo $start_time ?>").getTime();
const matchId = <?php echo $match_id; ?>; // Match ID for real-time updates

// Function to update the fight timer
function updateTimer() {
    const now = new Date().getTime();
    const timeLeft = startTime + 600000 - now;  // 600000ms = 10 minutes countdown

    if (timeLeft <= 0) {
        document.getElementById("countdown").innerHTML = "00:00";
        clearInterval(timerInterval);
    } else {
        const minutes = Math.floor(timeLeft / 60000);
        const seconds = Math.floor((timeLeft % 60000) / 1000);
        document.getElementById("countdown").innerHTML = minutes + ":" + (seconds < 10 ? "0" : "") + seconds;
    }
}

// Update the timer every second
const timerInterval = setInterval(updateTimer, 1000);

// Fetch match outcome, duration, and other details
function fetchMatchDetails() {
    fetch(`get_match_details.php?match_id=${matchId}`)
        .then(response => response.json())
        .then(data => {
            if (data.end_time) {
                // Update fight outcome if match is ended
                document.getElementById("fightStatus").innerHTML = "Match Ended";
                document.getElementById("fightOutcome").innerHTML = "Winner: " + data.winner;
                document.getElementById("matchDuration").innerHTML = "Total Fight Duration: " + data.duration;
            }
        })
        .catch(error => console.error('Error fetching match details:', error));
}

// Fetch match details every 5 seconds
setInterval(fetchMatchDetails, 5000);
