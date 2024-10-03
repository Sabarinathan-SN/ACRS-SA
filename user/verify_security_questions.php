<?php
session_start();
include 'header.php';
include 'db_connection.php'; 

if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $answer1 = $_POST['answer1'];
    $answer2 = $_POST['answer2'];
    $email = $_SESSION['email'];

    $sql = "SELECT one, two FROM T_reg WHERE Email = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Statement preparation failed: " . $conn->error);
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($correct_answer1, $correct_answer2);
    $stmt->fetch();

    if (password_verify($answer1, $correct_answer1) && password_verify($answer2, $correct_answer2)) {
        // Answers are correct, proceed to reset password
        header("Location: reset_password.php");
        exit();
    } else {
        // Answers are incorrect, redirect to send_security_code.php
        echo "<script>alert('Incorrect answers. You will be redirected to receive a security code via email.');</script>";
        header("Location: send_security_code.php");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Verify Security Questions</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-black">
        <div class="w-full max-w-md p-8 space-y-6 bg-gray-900 rounded-lg shadow-lg fade-in">
            <h2 class="text-3xl font-bold text-center text-white">Verify Security Questions</h2>
            <form class="mt-8 space-y-6" action="" method="POST">
                <div>
                    <label class="text-white">What is your favorite subject?</label>
                    <input name="answer1" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Answer">
                </div>
                <div>
                    <label class="text-white">Who is your favorite teacher?</label>
                    <input name="answer2" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Answer">
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Submit
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
