<?php
include 'header.php';
session_start();

if (!isset($_SESSION['instructions_followed']) || $_SESSION['instructions_followed'] !== true) {
    header("Location: instructions.php");
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

// Function to read status from JSON file
function getStatus($email) {
    $file = 'status.json';
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        return isset($data[$email]) ? $data[$email] : null;
    }
    return null;
}

// Function to update status in JSON file
function updateStatus($email, $reg_no, $status) {
    $file = 'status.json';
    if (file_exists($file)) {
        $json = file_get_contents($file);
        $data = json_decode($json, true);
    } else {
        $data = array();
    }
    $data[$email] = array("reg_no" => $reg_no, "status" => $status);
    file_put_contents($file, json_encode($data));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT Reg_no, Email, Password FROM T_reg WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }

    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($reg_no, $email, $hashed_password);
        $stmt->fetch();

        if (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $reg_no;
            $_SESSION['email'] = $email;

            // Check if the user is already in status.json
            $status = getStatus($email);
            if ($status === null) {
                // If not, add the user to status.json with status 'form.php'
                updateStatus($email, $reg_no, 'form.php');
                $status = 'form.php'; // Set status to form.php for first-time login
            }
            
            // Redirect based on status
            header("Location: " . $status['status']);
            exit();
        } else {
            echo "<script>alert('Invalid password.');</script>";
        }
    } else {
        echo "<script>alert('No user found with that email address.');</script>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000;
        }
        .fade-in {
            animation: fadeIn 2s ease-in;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-black">
        <div class="w-full max-w-md p-8 space-y-6 bg-gray-900 rounded-lg shadow-lg fade-in">
            <h2 class="text-3xl font-bold text-center text-white">Login</h2>
            <form class="mt-8 space-y-6" action="" method="POST">
                <div class="rounded-md shadow-sm -space-y-px">
                    <div>
                        <label for="email-address" class="sr-only">Email address</label>
                        <input id="email-address" name="email" type="email" autocomplete="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Email address">
                    </div>
                    <div>
                        <label for="password" class="sr-only">Password</label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Password">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    
                    <div class="text-sm">
                        <a href="register.php" class="font-medium text-indigo-600 hover:text-indigo-500">Register</a>
                    </div>
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Sign in
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
?>
