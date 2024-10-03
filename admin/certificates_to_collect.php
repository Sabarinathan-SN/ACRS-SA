<?php
// certificates_to_collect.php

include 'headerhome.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Fetch certificates to be collected
$certificates_sql = "SELECT r.Reg_no, ed.Certificate_no
    FROM registration r
    INNER JOIN examination_details ed ON r.Reg_no = ed.Reg_no
    WHERE ed.Status_of_passing = 'pass'";

$certificates_result = $conn->query($certificates_sql);

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificates to be Collected</title>
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
        <h2 class="text-center text-2xl mb-4">Certificates to be Collected</h2>
        
        <div class="flex justify-between items-center mb-4">
            <a href="admin_dashboard.php" class="button bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">Back to Dashboard</a>
        </div>

        <div class="flex justify-center mb-4">
            <table class="table-auto w-full">
                <thead>
                    <tr>
                        <th class="px-4 py-2">Registration Number</th>
                        <th class="px-4 py-2">Certificate Number</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($certificates_result->num_rows > 0) {
                        while($row = $certificates_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td class='border px-4 py-2'>" . $row["Reg_no"] . "</td>";
                            echo "<td class='border px-4 py-2'>" . $row["Certificate_no"] . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='2' class='text-center py-4'>No certificates to be collected at the moment.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
