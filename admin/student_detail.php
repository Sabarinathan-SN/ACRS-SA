<?php
include 'headerhome.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';

$reg_no = isset($_GET['reg_no']) ? $_GET['reg_no'] : '';

$student_sql = "SELECT s.Reg_no, s.Name, s.Dpt_id, s.Degree_id, s.Mobile_num, s.Mailid, s.Address, s.Dob, s.Adm_yr, 
    d.Dpt_name, de.Deg_name, r.Passout_year, r.Payment_status, r.Payment_date, r.Mode_of_collection, 
    ipc.Accompanying_persons, ipc.Food_preference, bp.Address_for_sending
    FROM Student s
    LEFT JOIN Department d ON s.Dpt_id = d.Dpt_id
    LEFT JOIN Degree de ON s.Degree_id = de.Deg_id
    LEFT JOIN Registration r ON s.Reg_no = r.Reg_no
    LEFT JOIN InPersonCollection ipc ON s.Reg_no = ipc.Reg_no
    LEFT JOIN ByPost bp ON s.Reg_no = bp.Reg_no
    WHERE s.Reg_no = ?";
$stmt = $conn->prepare($student_sql);
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$student_result = $stmt->get_result();
$student = $student_result->fetch_assoc();

$next_sql = "SELECT Reg_no FROM Student WHERE Reg_no > ? ORDER BY Reg_no ASC LIMIT 1";
$next_stmt = $conn->prepare($next_sql);
$next_stmt->bind_param("s", $reg_no);
$next_stmt->execute();
$next_result = $next_stmt->get_result();
$next = $next_result->fetch_assoc();

$prev_sql = "SELECT Reg_no FROM Student WHERE Reg_no < ? ORDER BY Reg_no DESC LIMIT 1";
$prev_stmt = $conn->prepare($prev_sql);
$prev_stmt->bind_param("s", $reg_no);
$prev_stmt->execute();
$prev_result = $prev_stmt->get_result();
$prev = $prev_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Detail</title>
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

        .detail-container {
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
            max-width: 800px;
            margin: 20px auto; /* Adjust for header and footer */
        }

        h2 {
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

        .details-table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-table th, .details-table td {
            border: 1px solid #fff;
            padding: 10px;
            text-align: left;
        }

        .details-table th {
            background-color: #4a5568;
            color: #edf2f7;
        }

        .details-table tr:nth-child(even) {
            background-color: #2d3748;
        }

        .details-table tr:hover {
            background-color: #4a5568;
        }

        .details-table td {
            background-color: #2d3748;
        }

        .details-table td, .details-table th {
            background-color: transparent;
        }

        .navigation {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .navigation a {
            background-color: #4a90e2;
            color: #fff;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
        }

        .navigation a:hover {
            background-color: #357ab8;
        }
    </style>
</head>
<body class="fade-in">
    <div class="detail-container">
        <div class="container">
            <h2>Student Detail</h2>
            <table class="details-table">
                <tr>
                    <th>Reg_no</th>
                    <td><?php echo $student['Reg_no']; ?></td>
                </tr>
                <tr>
                    <th>Name</th>
                    <td><?php echo $student['Name']; ?></td>
                </tr>
                <tr>
                    <th>Department</th>
                    <td><?php echo $student['Dpt_name']; ?></td>
                </tr>
                <tr>
                    <th>Degree</th>
                    <td><?php echo $student['Deg_name']; ?></td>
                </tr>
                <tr>
                    <th>Mobile Number</th>
                    <td><?php echo $student['Mobile_num']; ?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?php echo $student['Mailid']; ?></td>
                </tr>
                <tr>
                    <th>Address</th>
                    <td><?php echo $student['Address']; ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td><?php echo $student['Dob']; ?></td>
                </tr>
                <tr>
                    <th>Admission Year</th>
                    <td><?php echo $student['Adm_yr']; ?></td>
                </tr>
                <tr>
                    <th>Passout Year</th>
                    <td><?php echo $student['Passout_year']; ?></td>
                </tr>
                <tr>
                    <th>Payment Status</th>
                    <td><?php echo $student['Payment_status']; ?></td>
                </tr>
                <tr>
                    <th>Payment Date</th>
                    <td><?php echo $student['Payment_date']; ?></td>
                </tr>
                <tr>
                    <th>Mode of Collection</th>
                    <td><?php echo $student['Mode_of_collection']; ?></td>
                </tr>
                <tr>
                    <th>Accompanying Persons</th>
                    <td><?php echo $student['Accompanying_persons']; ?></td>
                </tr>
                <tr>
                    <th>Food Preference</th>
                    <td><?php echo $student['Food_preference']; ?></td>
                </tr>
                <tr>
                    <th>Address for Sending</th>
                    <td><?php echo $student['Address_for_sending']; ?></td>
                </tr>
            </table>

            <div class="navigation">
                <a href="student_detail.php?reg_no=<?php echo $prev['Reg_no']; ?>" <?php if (!$prev) echo 'style="display:none;"'; ?>>Previous</a>
                <a href="student_detail.php?reg_no=<?php echo $next['Reg_no']; ?>" <?php if (!$next) echo 'style="display:none;"'; ?>>Next</a>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
include 'footer.php';
?>
