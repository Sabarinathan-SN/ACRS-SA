<?php
include 'headerhome.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

// Fetch tickets and their corresponding student details
$tickets_sql = "
    SELECT t.Seat_id, s.Reg_no, s.Name, d.Dpt_name
    FROM ticket t
    JOIN Student s ON t.Reg_no = s.Reg_no
    JOIN Department d ON s.Dpt_id = d.Dpt_id";
$tickets_result = $conn->query($tickets_sql);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generated Tickets</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white font-sans">
    <div class="container mx-auto p-4">
        <h2 class="text-center text-2xl mb-4">Generated Tickets</h2>
        
        <?php if ($tickets_result->num_rows > 0): ?>
            <table class="min-w-full bg-gray-800">
                <thead>
                    <tr>
                        <th class="py-2">Seat Number</th>
                        <th class="py-2">Reg No</th>
                        <th class="py-2">Name</th>
                        <th class="py-2">Department</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($ticket_row = $tickets_result->fetch_assoc()): ?>
                        <tr>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($ticket_row['Seat_id']); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($ticket_row['Reg_no']); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($ticket_row['Name']); ?></td>
                            <td class="py-2 px-4"><?php echo htmlspecialchars($ticket_row['Dpt_name']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-center">No tickets generated yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
