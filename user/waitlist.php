<?php
session_start();

// Assuming users are already logged in and have a unique session identifier
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$jsonFilePath = 'C:\xampp\htdocs\acrs\admin\rem_seats.json';

// Fetch remaining seats and interest count
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $data = json_decode($jsonContent, true);
    $remainingSeats = $data['remaining_seats'];
    $interestCount = isset($data['interest_count']) ? $data['interest_count'] : 0;
} else {
    $remainingSeats = 0;
    $interestCount = 0;
}

// Check if the user has already shown interest
$userId = $_SESSION['user_id'];
$interestLogFile = 'interest_log.json';

if (file_exists($interestLogFile)) {
    $interestLog = json_decode(file_get_contents($interestLogFile), true);
} else {
    $interestLog = [];
}

$userHasShownInterest = isset($interestLog[$userId]);

if ($_SERVER["REQUEST_METHOD"] == "POST" && !$userHasShownInterest) {
    // Update interest count and mark user as interested
    $interestCount++;
    $interestLog[$userId] = true;

    // Save updated interest count back to the JSON file
    $data['interest_count'] = $interestCount;
    file_put_contents($jsonFilePath, json_encode($data, JSON_PRETTY_PRINT));

    // Save interest log
    file_put_contents($interestLogFile, json_encode($interestLog, JSON_PRETTY_PRINT));

    $userHasShownInterest = true;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waitlist</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-800 text-white">
    <div class="w-full max-w-md p-8 space-y-6 bg-gray-700 rounded-lg shadow-lg">
        <h2 class="text-3xl font-bold text-center">Waitlist</h2>
        <p class="text-xl">Remaining Seats: <?php echo $remainingSeats; ?></p>

        <form action="" method="POST">
            <button type="submit" <?php echo $userHasShownInterest ? 'disabled' : ''; ?> class="w-full py-2 px-4 mt-4 text-center bg-blue-500 hover:bg-blue-600 text-white font-bold rounded">
                <?php echo $userHasShownInterest ? 'Interest Added' : 'Add Your Interest'; ?>
            </button>
        </form>

        <p class="text-right mt-4">Number of people showing interest: <?php echo $interestCount; ?></p>
    </div>
</body>
</html>
