<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role_id = $_POST['role_id'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Check if the username or email already exists
    $sql_check = "SELECT Username FROM Admin WHERE Username = ? OR Email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ss", $username, $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        echo "<script>alert('Username or email already exists');</script>";
    } else {
        // Save the new admin details into a JSON file
        $registration_data = array(
            'username' => $username,
            'email' => $email,
            'password' => $hashed_password,
            'role_id' => $role_id
        );

        $file = 'admin_registrations.json';
        if (file_exists($file)) {
            $json_data = file_get_contents($file);
            $data_array = json_decode($json_data, true);
        } else {
            $data_array = array();
        }

        $data_array[] = $registration_data;
        $json_data = json_encode($data_array, JSON_PRETTY_PRINT);
        file_put_contents($file, $json_data);

        echo "<script>alert('Registration successful');</script>";
        header("Location: login.php");
        exit();
    }
    $stmt_check->close();
}

// Fetch roles excluding Super Admin
$sql_roles = "SELECT Role_id, Role_name FROM Roles WHERE Role_name != 'Super Admin'";
$result_roles = $conn->query($sql_roles);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
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
            <h2 class="text-4xl font-bold text-center">Register</h2>
            <form method="post" action="" class="space-y-6">
                <div class="flex flex-col">
                    <label for="username" class="text-lg form-label">Username</label>
                    <input type="text" id="username" name="username" required class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="email" class="text-lg form-label">Email</label>
                    <input type="email" id="email" name="email" required class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="password" class="text-lg form-label">Password</label>
                    <input type="password" id="password" name="password" required class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="role_id" class="text-lg form-label">Role</label>
                    <select id="role_id" name="role_id" required class="p-2 rounded-md form-input">
                        <?php while($row = $result_roles->fetch_assoc()): ?>
                            <option value="<?php echo $row['Role_id']; ?>"><?php echo $row['Role_name']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Register
                    </button>
                </div>
                <div>
                    <button type="button" onclick="window.location.href='login.php'" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-500 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 mt-4">
                        Back to Login
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
