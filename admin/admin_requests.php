<?php
include 'headerhome.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Fetch the admin's role from the database
$admin_id = $_SESSION['admin_id'];
$role_sql = "SELECT a.Role_id, r.Role_name FROM Admin a JOIN Roles r ON a.Role_id = r.Role_id WHERE a.Admin_id = ?";
$stmt = $conn->prepare($role_sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($role_id, $role_name);
$stmt->fetch();
$stmt->close();

// Check if the admin has the right to access this page
if ($role_id != 1) {
    echo "<script>alert('You do not have permission to access this page.'); window.location.href = 'admin_dashboard.php';</script>";
    exit();
}

$requests_file = 'admin_registrations.json';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];
    $hashed_password = $_POST['password'];

    // Insert the admin into the database
    $insert_sql = "INSERT INTO Admin (Username, Email, Password, Role_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("sssi", $username, $email, $hashed_password, $role_id);
    if ($stmt->execute()) {
        $stmt->close();

        // Remove the approved admin from the JSON file
        $json_data = file_get_contents($requests_file);
        $data_array = json_decode($json_data, true);
        foreach ($data_array as $key => $request) {
            if ($request['email'] == $email) {
                unset($data_array[$key]);
                break;
            }
        }
        $json_data = json_encode(array_values($data_array), JSON_PRETTY_PRINT);
        file_put_contents($requests_file, $json_data);

        echo "<script>alert('Admin approved successfully.');</script>";
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }
}

// Read the JSON file
if (file_exists($requests_file)) {
    $json_data = file_get_contents($requests_file);
    $requests = json_decode($json_data, true);
} else {
    $requests = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Requests</title>
    <style>
        body {
            background-color: #1a202c;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .fade-in {
            animation: fadeIn 2s ease-in;
        }

        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }

        .dashboard-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 100px); /* Adjust based on header and footer height */
        }

        .container {
            padding: 20px;
            background-color: #000;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            width: 90%;
            max-width: 1200px;
            margin: 20px auto; /* Adjust for header and footer */
        }

        h2 {
            text-align: center;
            margin-bottom: 16px;
        }

        p {
            text-align: center;
            margin-bottom: 16px;
        }

        .button {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 0.25rem;
            background-color: #4a90e2;
            color: #fff;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.3s;
            margin-bottom: 16px;
            text-decoration: none;
        }

        .button:hover {
            background-color: #357ab8;
        }

        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-bottom: 16px;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1200px; /* Ensures table scrolls if too wide */
        }

        .table th, .table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: center;
            white-space: nowrap;
        }

        .table th {
            background-color: #4a5568;
            color: #edf2f7;
        }

        .table tr:nth-child(even) {
            background-color: #2d3748;
        }

        .table tr:hover {
            background-color: #4a5568;
        }

        .table td {
            background-color: #2d3748;
        }

        .table td, .table th {
            background-color: transparent;
        }

        .individual-details-container {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 16px;
        }

        .individual-details-form select {
            padding: 0.5rem;
            border-radius: 0.25rem;
            border: none;
            margin-right: 8px;
        }

        .individual-details-form button {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 0.25rem;
            background-color: #4a90e2;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .individual-details-form button:hover {
            background-color: #357ab8;
        }
    </style>
</head>
<body class="fade-in">
    <div class="dashboard-container">
        <div class="container">
            <h2>Admin Requests</h2>
            <?php if (count($requests) > 0): ?>
                <div class="table-container">
                    <table class="table">
                        <tr>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                        <?php foreach ($requests as $request): ?>
                            <?php if ($request['role_id'] != 1): // Exclude Super Admin role ?>
                                <tr>
                                    <form method="POST" action="">
                                        <td><?php echo $request['username']; ?><input type="hidden" name="username" value="<?php echo $request['username']; ?>"></td>
                                        <td><?php echo $request['email']; ?><input type="hidden" name="email" value="<?php echo $request['email']; ?>"></td>
                                        <td><?php echo $request['role_id']; ?><input type="hidden" name="role_id" value="<?php echo $request['role_id']; ?>"></td>
                                        <td><input type="hidden" name="password" value="<?php echo $request['password']; ?>"><button type="submit" name="approve" class="button">Approve</button></td>
                                    </form>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php else: ?>
                <p>No admin registration requests.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
