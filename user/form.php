<?php
include 'headerhome.php';
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reg_no = $_SESSION['user_id'];
$passout_year = "";
$mode_of_collection = "";

// Fetch user's name and degree from the database
$sql = "SELECT Student.name, Degree.Deg_name FROM Student 
        LEFT JOIN Degree ON Student.Degree_id = Degree.Deg_id
        WHERE Student.Reg_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$stmt->bind_result($name, $degree_name);
$stmt->fetch();
$stmt->close();

// Check if the user has already entered details
$registration_details = array();
$file = 'registration_details.json';

if (file_exists($file)) {
    $json = file_get_contents($file);
    $registration_details = json_decode($json, true);
}

if (isset($registration_details[$reg_no])) {
    $passout_year = $registration_details[$reg_no]['passout_year'];
    $mode_of_collection = $registration_details[$reg_no]['mode_of_collection'];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $passout_year = $_POST['passout_year'];
    $mode_of_collection = $_POST['mode_of_collection'];

    // Save the registration details to the JSON file
    $registration_details[$reg_no] = array(
        "passout_year" => $passout_year,
        "mode_of_collection" => $mode_of_collection,
        "name" => $name,
        "reg_no" => $reg_no,
        "degree_name" => $degree_name
    );

    file_put_contents($file, json_encode($registration_details, JSON_PRETTY_PRINT));

    // Update status in status.json
    function updateStatus($email, $reg_no, $status) {
        $file = 'status.json';
        if (file_exists($file)) {
            $json = file_get_contents($file);
            $data = json_decode($json, true);
        } else {
            $data = array();
        }
        $data[$email] = array("reg_no" => $reg_no, "status" => $status);
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    }

    // Get the user's email from the session
    $email = $_SESSION['email'];

    // Set status based on the mode of collection
    $status = ($mode_of_collection == 'In Person') ? 'in_person.php' : 'by_post.php';
    updateStatus($email, $reg_no, $status);

    // Redirect based on mode of collection
    header("Location: " . $status);
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form</title>
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
            <h2 class="text-4xl font-bold text-center">Welcome</h2>
            <form action="" method="POST" class="space-y-6">
                <div class="flex flex-col">
                    <label for="name" class="text-lg form-label">Name</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" readonly class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="regno" class="text-lg form-label">Registration Number</label>
                    <input type="text" id="regno" name="regno" value="<?php echo htmlspecialchars($reg_no); ?>" readonly class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="degree" class="text-lg form-label">Degree</label>
                    <input type="text" id="degree" name="degree" value="<?php echo htmlspecialchars($degree_name); ?>" readonly class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="passout_year" class="text-lg form-label">Passout Year</label>
                    <input type="text" id="passout_year" name="passout_year" value="<?php echo htmlspecialchars($passout_year); ?>" required class="p-2 rounded-md form-input">
                </div>
                <div class="flex flex-col">
                    <label for="mode_of_collection" class="text-lg form-label">Mode of Collection</label>
                    <select id="mode_of_collection" name="mode_of_collection" class="p-2 rounded-md form-input">
                        <option value="In Person" <?php echo ($mode_of_collection == 'In Person') ? 'selected' : ''; ?>>In Person</option>
                        <option value="By Post" <?php echo ($mode_of_collection == 'By Post') ? 'selected' : ''; ?>>By Post</option>
                    </select>
                </div>
                <div class="flex justify-center">
                    <button type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">Save Details</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
include 'footer.php';
?>
