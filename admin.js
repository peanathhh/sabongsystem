let countdownInterval; // For the timer interval
let fightDuration = 600; // 10 minutes
let fastestKill = 600; // Default fastest kill time

// Start Match Event Listener
document.getElementById("matchForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const meron = document.getElementById("cock1").value;
    const wala = document.getElementById("cock2").value;

    if (meron && wala) {
        startMatch(meron, wala);
    } else {
        alert("Please enter both Meron (Red) and Wala (Blue) cock names!");
    }
});

// End Match Button Event Listener
document.getElementById("endMatch").addEventListener("click", function () {
    endMatch();
});

// Winner Announcement Event Listener
document.getElementById("winnerForm").addEventListener("submit", function (e) {
    e.preventDefault();
    const winner = document.getElementById("winner").value;

    if (winner) {
        announceWinner(winner);
    } else {
        alert("Please select a winner before announcing!");
    }
});

// Function to Start the Match
function startMatch(meron, wala) {
    fightDuration = 600; // Reset fight duration
    document.getElementById("fightStatus").textContent = `Fight Started: MERON (${meron}) vs WALA (${wala})`;

    // Reset UI for a new match
    document.getElementById("countdown").textContent = "10:00";
    document.getElementById("fightOutcome").textContent = "";
    document.getElementById("winnerForm").style.display = "block";

    // Update dropdown for winner selection
    document.getElementById("winner").innerHTML = `
        <option value="">Select Winner</option>
        <option value="Meron (${meron})">Meron (${meron})</option>
        <option value="Wala (${wala})">Wala (${wala})</option>
    `;

    // Enable End Match button
    document.getElementById("endMatch").disabled = false;

    // Start the timer
    clearInterval(countdownInterval); // Clear any previous timer
    countdownInterval = setInterval(updateTimer, 1000);
}

// Function to Update the Timer
function updateTimer() {
    if (fightDuration <= 0) {
        clearInterval(countdownInterval); // Stop the timer
        document.getElementById("countdown").textContent = "00:00";
        document.getElementById("fightStatus").textContent = "Time's up!";
    } else {
        fightDuration--;
        let minutes = Math.floor(fightDuration / 60);
        let seconds = fightDuration % 60;
        document.getElementById("countdown").textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }
}

// Function to End the Match
function endMatch() {
    clearInterval(countdownInterval); // Stop the timer
    const totalFightTime = 600 - fightDuration;

    let minutes = Math.floor(totalFightTime / 60);
    let seconds = totalFightTime % 60;

    document.getElementById("fightStatus").textContent = "Match Ended!";
    document.getElementById("fightOutcome").textContent = `Total Fight Duration: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    document.getElementById("endMatch").disabled = true;

    updateFastestKill(totalFightTime);
}

// Function to Announce the Winner
function announceWinner(winner) {
    document.getElementById("fightOutcome").textContent = `Winner: ${winner}`;
    document.getElementById("winnerForm").style.display = "none";

    // Display a popup alert with the winner
    alert(`🎉 The winner is: ${winner}! 🎉`);
}

// Function to Update Fastest Kill Report
function updateFastestKill(fightTime) {
    if (fightTime < fastestKill) {
        fastestKill = fightTime;
        let minutes = Math.floor(fastestKill / 60);
        let seconds = fastestKill % 60;
        document.getElementById("fastestKillReport").textContent = `Fastest Kill: ${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
    }
}
