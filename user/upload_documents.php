<?php
include 'header.php';
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reg_no = $_SESSION['user_id'];

// Create the main uploads directory if it doesn't exist
$main_upload_dir = 'D:\ACRS\uploads';
if (!is_dir($main_upload_dir)) {
    mkdir($main_upload_dir, 0777, true);
}

$target_dir = $main_upload_dir . DIRECTORY_SEPARATOR . $reg_no;

// Create a subdirectory for the student if it doesn't exist
if (!is_dir($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$existing_aadhar_card = $existing_tc = "";

// Check if documents already exist in the database
$sql_check_documents = "SELECT Aadhar_card, TC FROM Documents WHERE Reg_no = ?";
$stmt_check_documents = $conn->prepare($sql_check_documents);
$stmt_check_documents->bind_param("s", $reg_no);
$stmt_check_documents->execute();
$stmt_check_documents->bind_result($existing_aadhar_card, $existing_tc);
$stmt_check_documents->fetch();
$stmt_check_documents->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify'])) {
        // Update status to move to payment
        function updateStatus($email, $reg_no, $status) {
            $file = 'final.json';
            if (file_exists($file)) {
                $json = file_get_contents($file);
                $data = json_decode($json, true);
            } else {
                $data = array();
            }
            $data[$email] = array("reg_no" => $reg_no, "status" => $status);
            file_put_contents($file, json_encode($data));
        }

        // Fetch email from database
        $stmt = $conn->prepare("SELECT email FROM T_Reg WHERE Reg_no = ?");
        $stmt->bind_param("s", $reg_no);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();


        header("Location: final.php");
        exit();
    } else {
        $aadhar_card = $target_dir . DIRECTORY_SEPARATOR . basename($_FILES["aadhar_card"]["name"]);
        $tc = $target_dir . DIRECTORY_SEPARATOR . basename($_FILES["tc"]["name"]);

        // Move uploaded files to the target directory
        if (move_uploaded_file($_FILES["aadhar_card"]["tmp_name"], $aadhar_card) && move_uploaded_file($_FILES["tc"]["tmp_name"], $tc)) {
            $sql = "INSERT INTO Documents (Reg_no, Aadhar_card, TC) VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    Aadhar_card = VALUES(Aadhar_card),
                    TC = VALUES(TC)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $reg_no, $aadhar_card, $tc);
            $stmt->execute();
            $stmt->close();

            // Update status to verify details
            function updateStatus($email, $reg_no, $status) {
                $file = 'final.json';
                if (file_exists($file)) {
                    $json = file_get_contents($file);
                    $data = json_decode($json, true);
                } else {
                    $data = array();
                }
                $data[$email] = array("reg_no" => $reg_no, "status" => $status);
                file_put_contents($file, json_encode($data));
            }

            // Fetch email from database
            $stmt = $conn->prepare("SELECT email FROM T_Reg WHERE Reg_no = ?");
            $stmt->bind_param("s", $reg_no);
            $stmt->execute();
            $stmt->bind_result($email);
            $stmt->fetch();
            $stmt->close();

            updateStatus($email, $reg_no, "verify.php");

            echo "<script>alert('Documents uploaded successfully');</script>";
            header("Location: final.php");
            exit();
        } else {
            echo "<script>alert('Sorry, there was an error uploading your files.');</script>";
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Documents</title>
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
            <h2 class="text-4xl font-bold text-center">Upload Documents</h2>
            <form action="" method="POST" enctype="multipart/form-data" class="space-y-6">
                <?php if ($existing_aadhar_card && $existing_tc): ?>
                    <div class="text-center">
                        <p class="mb-4">Documents have already been uploaded.</p>
                        <button type="submit" name="verify" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Verify Details and Move to Payment</button>
                    </div>
                <?php else: ?>
                    <div class="flex flex-col">
                        <label for="aadhar_card" class="text-lg form-label">Aadhar Card</label>
                        <input type="file" id="aadhar_card" name="aadhar_card" class="p-2 rounded-md form-input" required>
                    </div>
                    <div class="flex flex-col">
                        <label for="tc" class="text-lg form-label">Transfer Certificate (TC)</label>
                        <input type="file" id="tc" name="tc" class="p-2 rounded-md form-input" required>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Upload Documents</button>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
?>
