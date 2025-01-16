<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <title>Scanner Data</title>
    <style>
        .container {
            display: flex;
            gap: 40px;
            padding: 20px;
        }

        /* Lap Count Table Styles */
        .lap-count {
            flex: 1;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
        }

        /* Add ranking styles */
        .rank {
            font-weight: bold;
            color: #333;
        }

        /* Scanner Display Styles */
        .scanner-display {
            flex: 1;
            border: 1px solid black;
            padding: 20px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .beacon-container {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .beacon {
            width: 40px;
            height: 40px;
            border: 2px solid black;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .latest-label {
            margin-top: 20px;
            font-style: italic;
        }
    </style>

    <script>
        $(document).ready(function() {
            function updateDisplay() {
                $.ajax({
                    url: "getminor.php",
                    type: 'GET',
                    success: function(response) {
                        const data = JSON.parse(response);
                        
                        // Sort players by count first, then by timestamp
                        let sortedPlayers = data.players.sort((a, b) => {
                            if (b.count !== a.count) {
                                return b.count - a.count; // First sort by count (descending)
                            }
                            // If counts are equal, sort by timestamp (ascending)
                            return new Date(a.first_timestamp) - new Date(b.first_timestamp);
                        });

                        // Update table
                        $("table tbody").empty();
                        $("table tbody").append("<tr><th>Rank</th><th>Player</th><th>Count</th></tr>");
                        
                        // Add sorted rows with rank
                        sortedPlayers.forEach((player, index) => {
                            $("table tbody").append(`
                                <tr>
                                    <td class="rank">${index + 1}</td>
                                    <td>${player.minor}</td>
                                    <td class='count'>${player.count}</td>
                                </tr>
                            `);
                        });

                        // Update beacon display with all beacons from database
                        $(".beacon-container").empty();
                        data.recent_beacons.forEach((minor) => {
                            $(".beacon-container").append(`
                                <div class="beacon">${minor}</div>
                            `);
                        });
                    },
                    error: function() {
                        console.log("Error occurred while fetching data.");
                    }
                });
            }

            // Call updateDisplay every second
            setInterval(updateDisplay, 1000);

            $("#clearButton").click(function() {
                $.ajax({
                    url: "clearData.php",
                    type: 'GET',
                    success: function(response) {
                        console.log(response);
                        $("table tbody").html("<tr><th>Rank</th><th>Player</th><th>Count</th></tr>");
                        $(".beacon-container").empty();
                    },
                    error: function(xhr, status, error) {
                        console.log("Error occurred while clearing data: " + xhr.responseText);
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="lap-count">
            <h2>Lap Count Ranking</h2>
            <table>
                <tbody>
                    <tr><th>Rank</th><th>Player</th><th>Count</th></tr>
                </tbody>
            </table>
            <button id="clearButton">Clear All Data</button>
        </div>

        <div class="scanner-display">
            <h2>Scanned</h2>
            <div class="beacon-container">
                <!-- Beacons will be dynamically added here -->
            </div>
            <div class="latest-label">Latest</div>
        </div>
    </div>
</body>
</html>