<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

$email = $_SESSION['email'];
$reg_no = $_SESSION['user_id'];


// Fetch the total cost from registration_details.json
$userDataJson = file_get_contents('registration_details.json');
$userData = json_decode($userDataJson, true);

$total_cost = 0;
if ($userData !== null) {
    foreach ($userData as $user) {
        if (isset($user['reg_no']) && $user['reg_no'] == $reg_no) {
            $total_cost = $user['total_cost'];
            break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $transaction_id = $_POST['transaction_id'];
    $upi_id = $_POST['upi_id'];

    $payment_data = [
        "date" => date("Y-m-d"),
        "time" => date("H:i:s"),
        "transaction_id" => $transaction_id,
        "reg_no" => $reg_no,
        "amount" => $total_cost,
        "status" => "unverified"
    ];

    // Write payment data to payment.json
    $payment_file = 'payment.json';
    $payment_json = file_exists($payment_file) ? json_decode(file_get_contents($payment_file), true) : [];
    $payment_json[] = $payment_data;
    file_put_contents($payment_file, json_encode($payment_json, JSON_PRETTY_PRINT));

    // Update status.json
    $status_file = 'status.json';
    $status_json = file_exists($status_file) ? json_decode(file_get_contents($status_file), true) : [];
    $status_json[$email] = ["reg_no" => $reg_no, "status" => "undercheck.php"];
    file_put_contents($status_file, json_encode($status_json, JSON_PRETTY_PRINT));

    // Redirect based on the payment status
    header("Location: undercheck.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
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
        .form-input {
            background-color: #edf2f7;
            color: #000;
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body class="fade-in">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-black text-white">
        <div class="max-w-md w-full space-y-8 form-container">
            <h2 class="text-4xl font-bold text-center">Payment Details</h2>
            <p class="text-xl text-center mb-4">Total Amount to be Paid: ₹<?php echo htmlspecialchars($total_cost); ?></p>
            <p class="text-lg text-center mb-4">UPI ID for Payment: convocation2.0@okptu</p>
            <form action="" method="POST" class="space-y-6">
                <div class="flex flex-col">
                    <label for="transaction_id" class="text-lg form-label">Transaction ID</label>
                    <input type="text" id="transaction_id" name="transaction_id" class="p-2 rounded-md form-input" required>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Proceed</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
