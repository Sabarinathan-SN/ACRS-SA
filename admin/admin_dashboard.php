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
$role_sql = "SELECT r.role_id FROM Admin a JOIN roles r ON a.role_id = r.role_id WHERE a.admin_id = ?";
$stmt = $conn->prepare($role_sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($role_id);
$stmt->fetch();
$stmt->close();

$student_sql = "SELECT s.Reg_no, s.Name, s.Dpt_id, s.Degree_id, s.Mobile_num, s.Mailid, s.Address, s.Dob, s.Adm_yr, 
    d.Dpt_name, de.Deg_name
    FROM Student s
    LEFT JOIN Department d ON s.Dpt_id = d.Dpt_id
    LEFT JOIN Degree de ON s.Degree_id = de.Deg_id";
$student_result = $conn->query($student_sql);

// Read registration status from reg_status.json
$reg_status_file = 'reg_status.json';
if (file_exists($reg_status_file)) {
    $reg_status = json_decode(file_get_contents($reg_status_file), true);
    $registration_closed = isset($reg_status['status']) && $reg_status['status'] === 'closed';
} else {
    $registration_closed = false;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .fade-in {
            animation: fadeIn 2s ease-in;
        }
        .button:hover {
            transform: scale(1.1);
        }
    </style>
</head>
<body class="bg-gray-900 text-white font-sans fade-in">
    <div class="container mx-auto p-4">
        <h2 class="text-center text-2xl mb-4">Welcome, <?php echo $_SESSION['username']; ?></h2>
        
        <div class="flex justify-between items-center mb-4">
            <a href="logout.php" class="button bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Logout</a>
            <div class="flex flex-col items-end space-y-4">
            <?php if ($role_id == 1 || $role_id == 2): ?>
                <a href="payment_approval.php" class="button bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Payment Approval</a>
            <?php endif; ?>
            <?php if ($role_id == 1): ?>
                <a href="admin_requests.php" class="button bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Admin Requests</a>
            <?php endif; ?>
            <?php if ($role_id == 1 || $role_id == 3): ?>
                <a href="certificates_to_collect.php" class="button bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">Certificates to be Collected</a>
            <?php endif; ?>
            <?php if ($role_id == 1): ?>
                <?php if (!$registration_closed): ?>
                    <button id="close-registration" class="button bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded">Close Registration and Generate Tickets</button>
                <?php else: ?>
                    <a href="display_tickets.php" class="button bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Display Tickets</a>
                <?php endif; ?>
            <?php endif; ?>
            </div>
        </div>

        <div class="flex justify-center mb-4">
            <form action="student_detail.php" method="GET" class="individual-details-form">
                <select name="reg_no" required class="bg-black text-white p-2 rounded mr-2">
                    <option value="">Select Student</option>
                    <?php
                    $student_result->data_seek(0); // Reset the result pointer to the start
                    if ($student_result->num_rows > 0) {
                        while($row = $student_result->fetch_assoc()) {
                            echo "<option value='".$row["Reg_no"]."'>".$row["Name"]." - ".$row["Reg_no"]."</option>";
                        }
                    }
                    ?>
                </select>
                <button type="submit" class="button bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">View Details</button>
            </form>
        </div>
    </div>
    <script>
        document.getElementById('close-registration').addEventListener('click', function() {
            let studentSeats = prompt('Enter number of seats for students:');
            let staffSeats = prompt('Enter number of seats for staff:');
            if (studentSeats && staffSeats) {
                window.location.href = 'generate_tickets.php?studentSeats=' + studentSeats + '&staffSeats=' + staffSeats;
            }
        });

        document.getElementById('display_tickets').addEventListener('click', function() {
            window.location.href = 'display_tickets.php';
        });
    </script>
</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
