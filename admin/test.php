<?php
include 'db_connect.php';
$registration_details = array();
$file = 'test.json';
$payment_json = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

if (file_exists($file)) {
    $json = file_get_contents($file);
    $details = json_decode($json, true);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $Name = $_POST['Name'];
    $price= $_POST['price'];
    $mobile = $_POST['mobile'];
    $bill_no = $_POST['bill_no'];
    $model = $_POST['model'];

    $details[$bill_no] = array(
        "id" => $id,
        "Name" => $Name,
        "date" => date("Y-m-d"),
        "price" => $price,
        "mobile" => $mobile,
        "bill_no" => $bill_no,
        "model" => $model,

    );
    $payment_json[] = $details;

    file_put_contents($file, json_encode($payment_json, JSON_PRETTY_PRINT));

   
     if (isset($_POST['approve'])) {
        
        $sql_registration = "INSERT INTO laptop (id, Price, Customer,payment_date,model,mob_number,bill_num) 
                             VALUES (?, ?, ?,?,?,?,?) 
                             ON DUPLICATE KEY UPDATE 
                             id = VALUES(id), 
                             Price = VALUES(price), Customer=VALUES(Customer), payment_date=VALUES(payment_date),model=VALUES(model),mob_number=VALUES(mob_number),bill_num=VALUES(bill_num)";
      //  $update_sql = "UPDATE laptop SET id = '?',Price=?,Customer=?, payment_date = ?, model = ?, mob_number = ? WHERE bill_num = ?";
        $stmt = $conn->prepare($sql_registration);
        $stmt->bind_param("iisdsii", $id, $price, $Name, $date,$model,$mobile,$bill_no);
        $stmt->execute();
        $stmt->close(); 
    }
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
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
            <h2 class="text-4xl font-bold text-center">Welcome</h2>
            <form action="" method="POST" class="space-y-6">
                
                <div class="flex flex-col">
                <label for="id" class="text-lg form-label">ID</label>
                <input type="text" id="id" name="id" class="p-2 rounded-md form-input" required>
                </div>
                <div class="flex flex-col">
                <label for="price" class="text-lg form-label">Price</label>
                <input type="text" id="price" name="price" class="p-2 rounded-md form-input" required>
                </div>
                <div class="flex flex-col">
                <label for="Name" class="text-lg form-label">Name</label>
                <input type="text" id="Name" name="Name" class="p-2 rounded-md form-input" required>
                </div>
                <div class="flex flex-col">
                <label for="model" class="text-lg form-label">Model</label>
                <input type="text" id="model" name="model" class="p-2 rounded-md form-input" required>
                </div>
                <div class="flex flex-col">
                <label for="bill_no" class="text-lg form-label">Bill number</label>
                <input type="text" id="bill_no" name="bill_no" class="p-2 rounded-md form-input" required>
                </div>
                <div class="flex flex-col">
                <label for="mobile" class="text-lg form-label">Mobile</label>
                <input type="text" id="mobile" name="mobile" class="p-2 rounded-md form-input" required>
                </div>
               
                <div class="flex justify-center">
                    <button type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Details</button>
                </div>
                <div class="flex justify-center">
                <button type='submit' name='approve' class='button'>Approve</button>
    </div>
            </form>
            
        </div>
    </div>
</body>
</html>
