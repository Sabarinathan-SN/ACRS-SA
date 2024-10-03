<?php
if (!isset($_POST['identifier']) || !isset($_POST['department'])) {
    die('Invalid request.');
}

$identifier = $_POST['identifier'];
$department = $_POST['department'];

$remSeatsFilePath = 'C:/xampp/htdocs/acrs/admin/rem_seats.json';
$notAttendingFilePath = 'C:/xampp/htdocs/acrs/admin/not_attending.json';

// Helper function to read and decode JSON files
function read_json_file($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }
    $content = file_get_contents($filePath);
    $json = json_decode($content, true);
    return is_array($json) ? $json : [];
}

// Read the rem_seats JSON file
$remSeats = read_json_file($remSeatsFilePath);

// Read the not_attending JSON file
$notAttending = read_json_file($notAttendingFilePath);

// Ensure notAttending is an array
if (!is_array($notAttending)) {
    die('Error reading not_attending data.');
}

// Check if the user has already marked as not attending
if (in_array($identifier, $notAttending)) {
    die('You have already marked as not attending.');
}

// Add the identifier to the not_attending list
$notAttending[] = $identifier;

// Update the rem_seats JSON
if (isset($remSeats[$department])) {
    $remSeats[$department]++;
} else {
    $remSeats[$department] = 1;
}

// Save the updated rem_seats JSON file
if (file_put_contents($remSeatsFilePath, json_encode($remSeats)) === false) {
    die('Failed to update rem_seats file.');
}

// Save the updated not_attending JSON file
if (file_put_contents($notAttendingFilePath, json_encode($notAttending)) === false) {
    die('Failed to update not_attending file.');
}

echo "Your not attending status has been recorded.";
?>
