<?php
include 'headerhome.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_id = $_POST['transaction_id'];
    $reg_no = $_POST['reg_no'];

    // Load the payment data from JSON file
    $payment_file = '../payment.json';
    $json_data = file_get_contents($payment_file);
    $payments = json_decode($json_data, true);

    if (isset($_POST['approve'])) {
        $date = $_POST['date'];
        $amount = $_POST['amount'];

        // Update the status in the payment.json file
        foreach ($payments as &$payment) {
            if ($payment['transaction_id'] == $transaction_id && $payment['reg_no'] == $reg_no) {
                $payment['status'] = 'verified';
                break;
            }
        }
        file_put_contents($payment_file, json_encode($payments, JSON_PRETTY_PRINT));

        // Update the Registration table
        $update_sql = "UPDATE Registration SET Payment_status = 'verified', Payment_date = ?, transaction_id = ?, amount = ? WHERE Reg_no = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssds", $date, $transaction_id, $amount, $reg_no);
        $stmt->execute();
        $stmt->close();

        // Load the status data from JSON file
        $status_file = 'C:/xampp/htdocs/acrs/status.json';
        $status_data = file_get_contents($status_file);
        $statuses = json_decode($status_data, true);

        // Update the status in the status.json file
        foreach ($statuses as &$status) {
            if ($status['reg_no'] == $reg_no) {
                $status['status'] = 'application.php';
                break;
            }
        }
        file_put_contents($status_file, json_encode($statuses, JSON_PRETTY_PRINT));
    } elseif (isset($_POST['reject'])) {
        // Update the status in the payment.json file
        foreach ($payments as &$payment) {
            if ($payment['transaction_id'] == $transaction_id && $payment['reg_no'] == $reg_no) {
                $payment['status'] = 'rejected';
                break;
            }
        }
        file_put_contents($payment_file, json_encode($payments, JSON_PRETTY_PRINT));

        // Load the status data from JSON file
        $status_file = 'C:/xampp/htdocs/acrs/status.json';
        $status_data = file_get_contents($status_file);
        $statuses = json_decode($status_data, true);

        // Update the status in the status.json file
        foreach ($statuses as &$status) {
            if ($status['reg_no'] == $reg_no) {
                $status['status'] = 'rejected.php';
                break;
            }
        }
        file_put_contents($status_file, json_encode($statuses, JSON_PRETTY_PRINT));

        // Update the Registration table
        $update_sql = "UPDATE Registration SET Payment_status = 'rejected' WHERE Reg_no = ? AND transaction_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ss", $reg_no, $transaction_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Load the payment data from JSON file
$payment_file = '../payment.json';
$json_data = file_get_contents($payment_file);
$payments = json_decode($json_data, true);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Approval</title>
    <style>
        body {
            background-color: #1a202c;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .fade-in {
            animation: fadeIn 2s ease-in;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .dashboard-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 100px);
        }

        .container {
            padding: 20px;
            background-color: #000;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 16px;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 16px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px;
        }

        .table th, .table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: center;
            white-space: nowrap;
        }

        .table th {
            background-color: #4a5568;
            color: #edf2f7;
        }

        .table tr:nth-child(even) {
            background-color: #2d3748;
        }

        .table tr:hover {
            background-color: #4a5568;
        }

        .table td {
            background-color: #2d3748;
        }

        .table td, .table th {
            background-color: transparent;
        }

        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.25rem;
            background-color: #4a90e2;
            color: #fff;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
            margin: 0.5rem;
        }

        .button:hover {
            background-color: #357ab8;
        }

        .approve-form, .reject-form {
            display: inline-block;
        }
    </style>
</head>
<body class="fade-in">
    <div class="dashboard-container">
        <div class="container">
            <h2>Payment Approvals</h2>
            
            <div class="table-container">
                <table class="table">
                    <tr>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Transaction ID</th>
                        <th>Registration Number</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                    <?php
                    if (!empty($payments)) {
                        foreach ($payments as $payment) {
                            if ($payment['status'] == 'unverified') {
                                echo "<tr>
                                    <td>".$payment['date']."</td>
                                    <td>".$payment['time']."</td>
                                    <td>".$payment['transaction_id']."</td>
                                    <td>".$payment['reg_no']."</td>
                                    <td>".$payment['amount']."</td>
                                    <td>".$payment['status']."</td>
                                    <td>
                                        <form class='approve-form' method='POST' action=''>
                                            <input type='hidden' name='transaction_id' value='".$payment['transaction_id']."'>
                                            <input type='hidden' name='reg_no' value='".$payment['reg_no']."'>
                                            <input type='hidden' name='date' value='".$payment['date']."'>
                                            <input type='hidden' name='amount' value='".$payment['amount']."'>
                                            <button type='submit' name='approve' class='button'>Approve</button>
                                        </form>
                                        <form class='reject-form' method='POST' action=''>
                                            <input type='hidden' name='transaction_id' value='".$payment['transaction_id']."'>
                                            <input type='hidden' name='reg_no' value='".$payment['reg_no']."'>
                                            <button type='submit' name='reject' class='button'>Reject</button>
                                        </form>
                                    </td>
                                </tr>";
                            }
                        }
                    } else {
                        echo "<tr><td colspan='7'>No unverified payments found</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
