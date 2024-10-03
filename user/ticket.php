<?php
session_start();
include 'db_connection.php'; // Including the database connection file

// Check if registration details are in session (using either reg_no or email)
if (isset($_SESSION['reg_no'])) {
    $identifier = $_SESSION['reg_no']; // Assuming reg_no is stored in session
} elseif (isset($_SESSION['email'])) {
    $identifier = $_SESSION['email']; // Assuming email is stored in session
} else {
    die('Session data not found.');
}

$jsonFilePath = 'registration_details.json';

// Function to fetch department name from the database
function fetchDepartmentName($reg_no, $conn) {
    $query = "SELECT d.dpt_name AS department_name
              FROM student s
              JOIN department d ON s.dpt_id = d.dpt_id
              WHERE s.reg_no = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $reg_no);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row ? $row['department_name'] : null;
}

// Read JSON file and find application details
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $registrations = json_decode($jsonContent, true);

    $ticketDetails = null;

    // Search for the ticket details using the identifier (reg_no or email) in session
    foreach ($registrations as $reg_no => $details) {
        if (($details['reg_no'] === $identifier || $details['email'] === $identifier) && isset($details['seat_number']) && isset($details['token_number'])) {
            // Fetch department name from database
            $conn = db_connect(); // Connect to the database
            $departmentName = fetchDepartmentName($details['reg_no'], $conn);
            $conn->close(); // Close the connection

            if ($departmentName) {
                $details['department'] = $departmentName; // Add department name to the details array
            }
            
            $ticketDetails = $details;
            break;
        }
    }

    if ($ticketDetails) {
        // Display the ticket details on the web page
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Convocation Ticket</title>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    background-color: #f0f0f0;
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                }
                .ticket {
                    width: 500px;
                    background-color: #fff;
                    border: 2px solid #007bff; /* Increased border size */
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0,0,0,0.1);
                    padding: 20px;
                    text-align: center;
                    position: relative; /* Position for seat banner */
                }
                .seat-banner {
                    position: absolute;
                    top: -20px;
                    right: -20px;
                    background-color: #007bff;
                    color: #fff;
                    padding: 5px 10px;
                    border-radius: 4px;
                    font-size: 14px;
                }
                .ticket-title {
                    font-size: 24px;
                    font-weight: bold;
                    margin-bottom: 20px;
                    color: #007bff;
                }
                .ticket-details {
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #ccc;
                    text-align: left;
                }
                .ticket-details p {
                    margin: 10px 0;
                    font-size: 16px;
                    line-height: 1.6;
                }
                .ticket-button {
                    margin-top: 20px;
                }
                .ticket-button button {
                    padding: 10px 20px;
                    background-color: #007bff;
                    color: #fff;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: background-color 0.3s ease;
                }
                .ticket-button button:hover {
                    background-color: #0056b3;
                }
                .not-attending-button {
                    margin-top: 10px;
                }
                .not-attending-button button {
                    padding: 10px 20px;
                    background-color: #dc3545;
                    color: #fff;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                    font-size: 16px;
                    transition: background-color 0.3s ease;
                }
                .not-attending-button button:hover {
                    background-color: #c82333;
                }
            </style>
        </head>
        <body>
            <div class="ticket">
                <div class="seat-banner">Seat ' . htmlspecialchars($ticketDetails['seat_number']) . '</div>
                <div class="ticket-title">Convocation Ticket</div>
                <div class="ticket-details">
                    <p><strong>Name:</strong> ' . htmlspecialchars($ticketDetails['name']) . ' &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <strong>Registration Number:</strong> ' . htmlspecialchars($ticketDetails['reg_no']) . '</p>
                    <p><strong>Degree Name:</strong> ' . htmlspecialchars($ticketDetails['degree_name']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($ticketDetails['email']) . '</p>
                    <p><strong>Passout Year:</strong> ' . htmlspecialchars($ticketDetails['passout_year']) . '</p>
                    <p><strong>Mode of Collection:</strong> ' . htmlspecialchars($ticketDetails['mode_of_collection']) . '</p>';

                    if ($ticketDetails['mode_of_collection'] === 'In Person') {
                        echo '<p><strong>Number of Accompanying Persons:</strong> ' . htmlspecialchars($ticketDetails['accompanying_persons']) . '</p>
                              <p><strong>Food Preference:</strong> ' . htmlspecialchars($ticketDetails['food_preference']) . '</p>';
                    }

                    echo '<p><strong>Total Cost:</strong> ' . htmlspecialchars($ticketDetails['total_cost']) . '</p>
                          <p><strong>Token Number:</strong> ' . htmlspecialchars($ticketDetails['token_number']) . '</p>
                          <p><strong>Department:</strong> ' . htmlspecialchars($ticketDetails['department']) . '</p>
                </div>
                <div class="ticket-button">
                    <button onclick="downloadPDF()">Download PDF</button>
                </div>
                <div class="not-attending-button">
                    <button id="not-attending-btn" onclick="markNotAttending()">Not Attending?</button>
                </div>
            </div>

            <script>
                function downloadPDF() {
                    window.location.href = "download_ticket_pdf.php";
                }

                function markNotAttending() {
                    var xhr = new XMLHttpRequest();
                    xhr.open("POST", "mark_not_attending.php", true);
                    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                    xhr.onreadystatechange = function () {
                        if (xhr.readyState === 4 && xhr.status === 200) {
                            alert(xhr.responseText);
                            document.getElementById("not-attending-btn").disabled = true; // Disable the button
                        }
                    };
                    xhr.send("identifier=' . urlencode($identifier) . '&department=' . urlencode($ticketDetails['department']) . '");
                }
            </script>
        </body>
        </html>';
    } else {
        die('Ticket details not found.');
    }
} else {
    die('Registration details JSON file not found.');
}
?>
