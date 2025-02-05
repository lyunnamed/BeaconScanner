<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="jquery.js"></script>
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
            padding: 8px;
            text-align: center;
        }

        @keyframes blink {
            0% { background-color: yellow; }
            50% { background-color: transparent; }
            100% { background-color: yellow; }
        }

        .blinking {
            animation: blink 2s infinite;
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
            flex-direction: row-reverse;
            justify-content: flex-end;
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
        $(function() {
            function updateDisplay() {
                $.ajax({
                    url: "getminor.php",
                    type: 'GET',
                    success: function(response) {
                        const data = JSON.parse(response);
                        const existingMinors = {};

                        // Update existing rows and track existing minors
                        $("tr").each(function() {
                            const minorCell = $(this).find("td:first");
                            const occurrenceCell = $(this).find(".count");
                            const minorValue = minorCell.text().trim();

                            if (data.counts[minorValue]) {
                                existingMinors[minorValue] = true;
                                const newOccurrence = data.counts[minorValue];

                                if (occurrenceCell.text().trim() !== newOccurrence.toString()) {
                                    occurrenceCell.text(newOccurrence);
                                    // Changed to prepend
                                    $(".beacon-container").prepend(`<div class="beacon">${minorValue}</div>`);
                                    if (occurrenceCell.css('background-color') === 'rgb(255, 255, 0)') {
                                        occurrenceCell.css('background-color', '#ADD8E6');
                                    } else {
                                        occurrenceCell.css('background-color', 'yellow');
                                    }
                                }
                            }
                        });

                        // Add new rows for any new minors
                        for (const minor in data.counts) {
                            if (!existingMinors[minor]) {
                                $("table").append(`<tr><td>${minor}</td><td class='count'>${data.counts[minor]}</td></tr>`);
                                // Changed to prepend
                                $(".beacon-container").prepend(`<div class="beacon">${minor}</div>`);
                            }
                        }

                        // Update beacon container with all scans
                        if (data.scans && data.scans.length > 0) {
                            data.scans.forEach(minor => {
                                // Changed to prepend
                                $(".beacon-container").prepend(`<div class="beacon">${minor}</div>`);
                            });
                        }
                    },
                    error: function() {
                        console.log("Error occurred while fetching data.");
                    }
                });
            }

            // Call updateDisplay every second
            setInterval(updateDisplay, 1000);

            // Clear button functionality
            $("#clearButton").click(function() {
                $.ajax({
                    url: "clearData.php",
                    type: 'GET',
                    success: function(response) {
                        console.log(response);
                        // Clear both the table and beacon container
                        $("table").find("tr:gt(0)").remove();
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
                <tr><th>Minor</th><th>Occurrence</th></tr>
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
