<?php
session_start();
include 'header.php';

if (!isset($_SESSION['security_code'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_code = $_POST['security_code'];

    if ($entered_code == $_SESSION['security_code']) {
        header("Location: reset_password.php");
        exit();
    } else {
        echo "<script>alert('Invalid security code.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Security Code</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-black">
        <div class="w-full max-w-md p-8 space-y-6 bg-gray-900 rounded-lg shadow-lg fade-in">
            <h2 class="text-3xl font-bold text-center text-white">Verify Security Code</h2>
            <form class="mt-8 space-y-6" action="" method="POST">
                <div>
                    <label for="security_code" class="sr-only">Security Code</label>
                    <input id="security_code" name="security_code" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Security Code">
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Verify
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>

