<?php
session_start();
include 'header.php';
include 'db_connection.php'; // Include your database connection script

if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
    $email = $_SESSION['email'];

    $sql = "UPDATE T_reg SET Password = ? WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("ss", $new_password, $email);
    if ($stmt->execute()) {
        echo "<script>alert('Password successfully reset.');</script>";
        header("Location: login.php");
        exit();
    } else {
        echo "<script>alert('Failed to reset password. Please try again.');</script>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-black">
        <div class="w-full max-w-md p-8 space-y-6 bg-gray-900 rounded-lg shadow-lg fade-in">
            <h2 class="text-3xl font-bold text-center text-white">Reset Password</h2>
            <form class="mt-8 space-y-6" action="" method="POST">
                <div>
                    <label for="new_password" class="sr-only">New Password</label>
                    <input id="new_password" name="new_password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="New Password">
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
