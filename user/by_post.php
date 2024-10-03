<?php
include 'headerhome.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "sn";
$password = "1234";
$dbname = "convocation";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$reg_no = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['address_for_sending']) && !empty($_POST['address_for_sending'])) {
        $address_for_sending = $_POST['address_for_sending'];



        // Function to update status in status.json
        function updateStatus($email, $reg_no, $status) {
            $file = 'status.json';
            if (file_exists($file)) {
                $json = file_get_contents($file);
                $data = json_decode($json, true);
            } else {
                $data = array();
            }
            $data[$email] = array("reg_no" => $reg_no, "status" => $status);
            file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        }

        // Fetch email from database
        $stmt = $conn->prepare("SELECT email FROM T_Reg WHERE Reg_no = ?");
        $stmt->bind_param("s", $reg_no);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();

        // Update status in status.json
        updateStatus($email, $reg_no, "payment.php");

        // Calculate or retrieve total_cost (example: sum of accompanying_persons * cost_per_person)
        // Example calculation:
      
        $total_cost = 1300;

        // Update or add entry in registration_details.json based on mode_of_collection
        $json_file = 'registration_details.json';
        $json_data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

        // Check if mode_of_collection is by post
        if (!isset($json_data[$reg_no])) {
            // If entry doesn't exist, create new entry
            $json_data[$reg_no] = array(
                "email" => $email,
                "mode_of_collection" => "by post",
                "address_for_sending" => $address_for_sending,
                "total_cost" => $total_cost  // Add total_cost field
            );
        } else {
            // If entry exists, update only mode_of_collection, email, and address_for_sending
            $json_data[$reg_no]['mode_of_collection'] = "by post";
            $json_data[$reg_no]['email'] = $email;
            $json_data[$reg_no]['address_for_sending'] = $address_for_sending;
            $json_data[$reg_no]['total_cost'] = $total_cost; // Update total_cost field

            // Keep existing fields intact (accompanying_persons, food_preference, etc.)
            // Example:
            // $json_data[$reg_no]['accompanying_persons'] = "3";
            // $json_data[$reg_no]['food_preference'] = "Veg";
        }

        // Save updated JSON data back to the file
        file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT));

        echo "<script>alert('Details for by-post collection saved successfully');</script>";
        header("Location: upload_documents.php");
        exit();
    } else {
        echo "<script>alert('Address for sending is required');</script>";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>By Post Collection</title>
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
            <h2 class="text-4xl font-bold text-center">By Post Collection Details</h2>
            <form action="" method="POST" class="space-y-6">
                <div class="flex flex-col">
                    <label for="address_for_sending" class="text-lg form-label">Address for Sending</label>
                    <textarea id="address_for_sending" name="address_for_sending" class="p-2 rounded-md form-input" required></textarea>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Details</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
?>
