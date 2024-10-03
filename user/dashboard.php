<?php
include 'headerhome.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "sn";
$password = "1234";
$dbname = "convocation";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$reg_no = $_SESSION['user_id'];

// Fetch the name from the Student table using the registration number
$sql_name = "SELECT Name FROM Student WHERE Reg_no = ?";
$stmt_name = $conn->prepare($sql_name);
$stmt_name->bind_param("i", $reg_no);
$stmt_name->execute();
$stmt_name->bind_result($name);
$stmt_name->fetch();
$stmt_name->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $degree_id = $_POST['degree_id'];
    $passout_year = $_POST['passout_year'];
    $mode_of_collection = $_POST['mode_of_collection'];

    $sql = "INSERT INTO Registration (Reg_no, Mode_of_collection) VALUES (?, ?) ON DUPLICATE KEY UPDATE Mode_of_collection=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $reg_no, $mode_of_collection, $mode_of_collection);
    $stmt->execute();
    $stmt->close();

    echo "<script>alert('Details saved successfully');</script>";
}

$sql = "SELECT Degree.Deg_name, Examination_Details.Status_of_passing, Registration.Mode_of_collection FROM Student 
        LEFT JOIN Degree ON Student.Degree_id = Degree.Deg_id
        LEFT JOIN Examination_Details ON Student.Reg_no = Examination_Details.Reg_no
        LEFT JOIN Registration ON Student.Reg_no = Registration.Reg_no
        WHERE Student.Reg_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $reg_no);
$stmt->execute();
$stmt->bind_result($degree_name, $passout_status, $mode_of_collection);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #000;
            color: #fff;
        }
        .fade-in {
            animation: fadeIn 2s ease-in;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="fade-in">
    <div class="min-h-screen py-12 px-4 sm:px-6 lg:px-8 bg-black text-white">
        <div class="max-w-7xl mx-auto space-y-8">
            <h2 class="text-4xl font-bold text-center">Welcome, <?php echo htmlspecialchars($name); ?></h2> <!-- Display the fetched name here -->
            <div class="space-y-6">
                <form action="" method="POST" class="space-y-4">
                    <div class="flex flex-col">
                        <label for="regno" class="text-lg">Registration Number</label>
                        <input type="text" id="regno" name="regno" value="<?php echo htmlspecialchars($reg_no); ?>" readonly class="p-2 text-black rounded-md">
                    </div>
                    <div class="flex flex-col">
                        <label for="degree" class="text-lg">Degree</label>
                        <input type="text" id="degree" name="degree" value="<?php echo htmlspecialchars($degree_name); ?>" readonly class="p-2 text-black rounded-md">
                    </div>
                    <div class="flex flex-col">
                        <label for="passout_year" class="text-lg">Passout Year</label>
                        <input type="text" id="passout_year" name="passout_year" value="<?php echo htmlspecialchars($passout_status); ?>" class="p-2 text-black rounded-md">
                    </div>
                    <div class="flex flex-col">
                        <label for="mode_of_collection" class="text-lg">Mode of Collection</label>
                        <select id="mode_of_collection" name="mode_of_collection" class="p-2 text-black rounded-md">
                            <option value="In Person" <?php echo $mode_of_collection == 'In Person' ? 'selected' : ''; ?>>In Person</option>
                            <option value="By Post" <?php echo $mode_of_collection == 'By Post' ? 'selected' : ''; ?>>By Post</option>
                        </select>
                    </div>
                    <div class="flex justify-center">
                        <button type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Details</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
?>
