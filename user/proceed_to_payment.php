<?php
session_start();



$email = isset($_POST['email']) ? $_POST['email'] : '';

if (empty($email)) {
    echo "Email not provided.";
    exit();
}

// Fetch the total cost from user_data.json
$userDataJson = file_get_contents('user_data.json');
$userData = json_decode($userDataJson, true);

$total_cost = 0;
foreach ($userData as $user) {
    if ($user['email'] == $email) {
        $total_cost = $user['total_cost'];
        break;
    }
}

// Redirect to the payment page with the total cost as a query parameter
header("Location: payment.php?total_cost=" . urlencode($total_cost));
exit();
?>
