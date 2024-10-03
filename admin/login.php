<?php
include 'headerhome.php';
session_start();
include 'db_connect.php';

// Path to the JSON file
$jsonFilePath = 'admin_registrations.json';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Read the JSON file
    if (file_exists($jsonFilePath)) {
        $jsonContent = file_get_contents($jsonFilePath);
        $registrations = json_decode($jsonContent, true);

        // Check if the username is in the JSON file
        foreach ($registrations as $registration) {
            if ($registration['username'] === $username) {
                echo "<script>alert('Kindly wait. Your details are being verified.');</script>";
                exit();
            }
        }
    }

    // Prepare the SQL query to fetch the user details
    $sql = "SELECT a.Admin_id, a.Username, a.Password, r.Role_name 
            FROM Admin a
            JOIN Roles r ON a.Role_id = r.Role_id
            WHERE a.Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($admin_id, $db_username, $db_password, $role_name);
    $stmt->fetch();
    $stmt->close();

    // Verify the password
    if ($db_username && password_verify($password, $db_password)) {
        // Password is correct, set session variables
        $_SESSION['admin_id'] = $admin_id;
        $_SESSION['username'] = $db_username;
        $_SESSION['role'] = $role_name;
        header("Location: admin_dashboard.php");
        exit();
    } else {
        // Invalid credentials
        echo "<script>alert('Invalid credentials');</script>";
    }
}
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
            <h2 class="text-4xl font-bold text-center">Admin Login</h2>
            <form method="post" action="" class="space-y-6">
                <div class="flex flex-col">
                    <label for="username" class="text-lg form-label">Username</label>
                    <input type="text" id="username" name="username" required class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="password" class="text-lg form-label">Password</label>
                    <input type="password" id="password" name="password" required class="p-2 rounded-md form-input">
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Login
                    </button>
                </div>
                <div>
                    <button type="button" onclick="window.location.href='register.php'" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-500 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Register
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
