<?php
require_once('vendor/autoload.php'); // Include TCPDF library

session_start();

$jsonFilePath = 'registration_details.json';

// Check if registration details are in session
if (isset($_SESSION['reg_no'])) {
    $identifier = $_SESSION['reg_no']; // Assuming reg_no is stored in session
} elseif (isset($_SESSION['email'])) {
    $identifier = $_SESSION['email']; // Assuming email is stored in session
} else {
    die('Session data not found.');
}

// Read JSON file and find application details
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $registrations = json_decode($jsonContent, true);

    $details = null;

    // Search for the identifier in the JSON data
    foreach ($registrations as $reg) {
        if ($reg['reg_no'] === $identifier || $reg['email'] === $identifier) {
            $details = $reg;
            break;
        }
    }

    if ($details) {
        // Display the details on the web page
        echo '
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Application Details</title>
            <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
            <style>
                body {
                    background-color: #1a202c;
                    color: #fff;
                }
                .fade-in {
                    animation: fadeIn 2s ease-in;
                }
                @keyframes fadeIn {
                    0% { opacity: 0; }
                    100% { opacity: 1; }
                }
                .form-container {
                    background-color: #2d3748;
                    border-radius: 0.5rem;
                    padding: 2rem;
                }
            </style>
        </head>
        <body class="fade-in">
            <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-black text-white">
                <div class="max-w-md w-full space-y-8 form-container">
                    <h2 class="text-4xl font-bold text-center">Application Details</h2>
                    <div class="space-y-6">
                        <p><strong>Name:</strong> ' . htmlspecialchars($details['name']) . '</p>
                        <p><strong>Registration Number:</strong> ' . htmlspecialchars($details['reg_no']) . '</p>
                        <p><strong>Degree Name:</strong> ' . htmlspecialchars($details['degree_name']) . '</p>
                        <p><strong>Passout Year:</strong> ' . htmlspecialchars($details['passout_year']) . '</p>
                        <p><strong>Mode of Collection:</strong> ' . htmlspecialchars($details['mode_of_collection']) . '</p>';

                        if ($details['mode_of_collection'] === 'In Person') {
                            echo '<p><strong>Number of Accompanying Persons:</strong> ' . htmlspecialchars($details['accompanying_persons']) . '</p>
                                  <p><strong>Food Preference:</strong> ' . htmlspecialchars($details['food_preference']) . '</p>';
                        } elseif ($details['mode_of_collection'] === 'by post') {
                            echo '<p><strong>Address for Sending:</strong> ' . htmlspecialchars($details['address_for_sending']) . '</p>';
                        }

                        echo '<p><strong>Email:</strong> ' . htmlspecialchars($details['email']) . '</p>
                              <p><strong>Total Cost:</strong> ' . htmlspecialchars($details['total_cost']) . '</p>
                    </div>
                    <div>
                        <button onclick="downloadPDF()" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Download PDF
                        </button>
                    </div>
                </div>
            </div>
            <script>
                function downloadPDF() {
                    window.location.href = "download_pdf.php";
                }
            </script>
        </body>
        </html>';
    } else {
        die('Application details not found.');
    }
} else {
    die('Registration details JSON file not found.');
}
?>
