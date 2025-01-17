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
            gap: 20px;
            padding: 10px;
            height: 60vh;   
            min-height: 300px;
            max-height: 600px;
        }

        /* Lap Count Table Styles */
        .lap-count {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        table, th, td {
            border: 1px solid black;
            padding: 4px;
            text-align: center;
            font-size: clamp(12px, 1.5vh, 14px);
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
            padding: 10px;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .beacon-container {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
            overflow-y: auto;
            max-height: calc(100% - 50px);
            align-content: flex-start;
            padding-right: 5px;
        }

        .beacon {
            width: clamp(25px, 4vh, 35px);
            height: clamp(25px, 4vh, 35px);
            border: 1px solid black;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 3px;
            flex-shrink: 0;
            font-size: clamp(10px, 1.2vh, 14px);
        }

        .latest-label {
            margin-top: 10px;
            font-style: italic;
            flex-shrink: 0;
            font-size: clamp(10px, 1.2vh, 12px);
        }

        h2 {
            font-size: clamp(14px, 1.8vh, 16px);
            margin: 0 0 10px 0;
        }

        #clearButton {
            margin-top: 10px;
            padding: 4px 8px;
            font-size: clamp(10px, 1.2vh, 12px);
        }

        /* Custom scrollbar styles */
        .beacon-container::-webkit-scrollbar {
            width: 8px;
        }

        .beacon-container::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        .beacon-container::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .beacon-container::-webkit-scrollbar-thumb:hover {
            background: #555;
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
