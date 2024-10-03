<?php
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reg_no = $_POST['reg_no'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $favourite_subject = $_POST['favourite_subject'];
    $favourite_teacher = $_POST['favourite_teacher'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check the Status_of_passing in examination_details table
        $status_check_sql = "SELECT Status_of_passing FROM examination_details WHERE Reg_no = ?";
        $status_check_stmt = $conn->prepare($status_check_sql);
        if (!$status_check_stmt) {
            die("Statement preparation failed: " . $conn->error);
        }
        $status_check_stmt->bind_param("s", $reg_no);
        $status_check_stmt->execute();
        $status_check_result = $status_check_stmt->get_result();

        if ($status_check_result->num_rows > 0) {
            $row = $status_check_result->fetch_assoc();
            $status_of_passing = $row['Status_of_passing'];

            if ($status_of_passing == 'Fail') {
                echo "<script>alert('You have not qualified to receive a certificate.');</script>";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $sql = "INSERT INTO T_reg (Reg_no, Email, Password, one, two) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if (!$stmt) {
                    die("Statement preparation failed: " . $conn->error);
                }
                $stmt->bind_param("sssss", $reg_no, $email, $hashed_password, $favourite_subject, $favourite_teacher);
                if ($stmt->execute()) {
                    $stmt->close();
                    $conn->close();

                    // Check registration status
                    $reg_status_file = 'C:\xampp\htdocs\acrs\admin\reg_status.json';
                    if (file_exists($reg_status_file)) {
                        $reg_status = json_decode(file_get_contents($reg_status_file), true);
                    } else {
                        $reg_status = ['status' => 'open'];
                    }

                    // Update status.json based on registration status
                    $status_file = 'status.json';
                    if (file_exists($status_file)) {
                        $status_data = json_decode(file_get_contents($status_file), true);
                    } else {
                        $status_data = [];
                    }

                    if ($reg_status['status'] == 'closed') {
                        $status_data[$email] = [
                            'reg_no' => $reg_no,
                            'status' => 'waitlist.php'
                        ];
                    } else {
                        $status_data[$email] = [
                            'reg_no' => $reg_no,
                            'status' => 'form.php'
                        ];
                    }

                    file_put_contents($status_file, json_encode($status_data, JSON_PRETTY_PRINT));

                    // Redirect to login form
                    header("Location: login.php");
                    exit();
                } else {
                    echo "<script>alert('Error: " . $stmt->error . "');</script>";
                    $stmt->close();
                }
            }
        } else {
            echo "<script>alert('Registration number not found.');</script>";
        }

        $status_check_stmt->close();
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        .form-grid {
            display: grid;
            gap: 1rem;
            align-items: center;
        }
        .form-item {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .form-item-full {
            grid-column: span 1;
        }
        .form-item label {
            white-space: nowrap;
        }
        .form-input {
            appearance: none;
            border-radius: 0.375rem;
            width: 100%;
            padding: 0.45rem;
            border: 1px solid #d1d5db;
            placeholder-color: #6b7280;
            text-color: #1f2937;
            focus-outline: none;
            focus-ring: 2;
            focus-ring-color: #6366f1;
            focus-border-color: #6366f1;
            text-sm: sm;
        }
    </style>
    <script>
        function validateForm() {
            const password = document.getElementById("password").value;
            const confirmPassword = document.getElementById("confirm-password").value;
            if (password !== confirmPassword) {
                alert("Passwords do not match!");
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="flex items-center justify-center min-h-screen bg-black">
    <div class="w-full max-w-md p-8 space-y-6 bg-gray-800 rounded-lg shadow-lg fade-in">
        <h2 class="text-3xl font-bold text-center text-white">Register</h2>
        <form class="mt-8 space-y-6" action="" method="POST" onsubmit="return validateForm()">
            <div class="form-grid">
                <div class="form-item">
                    <label for="reg-no" class="text-white">Registration Number</label>
                    <input id="reg-no" name="reg_no" type="text" autocomplete="reg-no" required class="form-input">
                </div>
                <div class="form-item">
                    <label for="email-address" class="text-white">Email address</label>
                    <input id="email-address" name="email" type="email" autocomplete="email" required class="form-input">
                </div>
                <div class="form-item">
                    <label for="password" class="text-white">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required class="form-input">
                </div>
                <div class="form-item">
                    <label for="confirm-password" class="text-white">Confirm Password</label>
                    <input id="confirm-password" name="confirm_password" type="password" required class="form-input">
                </div>
                <div class="form-item-full">
                    <label class="text-white">Security Questions</label>
                </div>
                <div class="form-item">
                    <label for="favourite-subject" class="text-white">Favorite Subject</label>
                    <input id="favourite-subject" name="favourite_subject" type="text" required class="form-input">
                </div>
                <div class="form-item">
                    <label for="favourite-teacher" class="text-white">Favorite Teacher</label>
                    <input id="favourite-teacher" name="favourite_teacher" type="text" required class="form-input">
                </div>
            </div>
            <div>
                <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Register
                </button>
            </div>
        </form>
    </div>
</body>
</html>
