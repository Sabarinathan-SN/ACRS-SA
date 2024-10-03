<?php
include 'header.php';
include 'db_connection.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$reg_no = $_SESSION['user_id'];
$accompanying_persons = 0;
$food_preference = "";

// Fetch user's email from the database
$stmt = $conn->prepare("SELECT email FROM T_Reg WHERE Reg_no = ?");
$stmt->bind_param("s", $reg_no);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Fetch existing details from JSON if available
$json_file = 'registration_details.json';
$json_data = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : [];

// Check if registration details already exist
if (isset($json_data[$reg_no])) {
    // Update existing details
    if (isset($_POST['accompanying_persons']) && isset($_POST['food_preference'])) {
        $accompanying_persons = $_POST['accompanying_persons'];
        $food_preference = $_POST['food_preference'];

        if ($accompanying_persons > 3) {
            echo "<script>alert('The number of accompanying persons cannot exceed three.');</script>";
        } else {
            $food_cost_per_person = $food_preference == 'Veg' ? 150 : 200;
            $food_cost = $food_cost_per_person * $accompanying_persons;
            $total_cost = 1000 + $food_cost; // Fixed amount of 1000 plus food cost

            // Update JSON data
            $json_data[$reg_no]['email'] = $email;
            $json_data[$reg_no]['accompanying_persons'] = $accompanying_persons;
            $json_data[$reg_no]['food_preference'] = $food_preference;
            $json_data[$reg_no]['total_cost'] = $total_cost;

            file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT));

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

            // Set status for upload documents
            updateStatus($email, $reg_no, "upload_documents.php");

            echo "<script>alert('Details for in-person collection updated successfully');</script>";
            header("Location: upload_documents.php");
            exit();
        }
    }
} else {
    // Add new registration details
    if (isset($_POST['accompanying_persons']) && isset($_POST['food_preference'])) {
        $accompanying_persons = $_POST['accompanying_persons'];
        $food_preference = $_POST['food_preference'];

        if ($accompanying_persons > 3) {
            echo "<script>alert('The number of accompanying persons cannot exceed three.');</script>";
        } else {
            $food_cost_per_person = $food_preference == 'Veg' ? 150 : 200;
            $food_cost = $food_cost_per_person * $accompanying_persons;
            $total_cost = 1000 + $food_cost; // Fixed amount of 1000 plus food cost

            // Create new entry in JSON data
            $json_data[$reg_no] = array(
                "email" => $email,
                "accompanying_persons" => $accompanying_persons,
                "food_preference" => $food_preference,
                "total_cost" => $total_cost
            );

            file_put_contents($json_file, json_encode($json_data, JSON_PRETTY_PRINT));

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

            // Set status for upload documents
            updateStatus($email, $reg_no, "upload_documents.php");

            echo "<script>alert('Details for in-person collection saved successfully');</script>";
            header("Location: upload_documents.php");
            exit();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Person Collection</title>
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
    <script>
        function validateForm() {
            const accompanyingPersons = document.getElementById("accompanying_persons").value;
            if (accompanyingPersons > 3) {
                alert("The number of accompanying persons cannot exceed three.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body class="fade-in">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-black text-white">
        <div class="max-w-md w-full space-y-8 form-container">
            <h2 class="text-4xl font-bold text-center">In-Person Collection Details</h2>
            <form action="" method="POST" class="space-y-6" onsubmit="return validateForm()">
                <div class="flex flex-col">
                    <label for="accompanying_persons" class="text-lg form-label">Number of Accompanying Persons</label>
                    <input type="number" id="accompanying_persons" name="accompanying_persons" class="p-2 rounded-md form-input" value="<?php echo htmlspecialchars($accompanying_persons); ?>" required>
                </div>
                <div class="flex flex-col">
                    <label for="food_preference" class="text-lg form-label">Food Preference</label>
                    <select id="food_preference" name="food_preference" class="p-2 rounded-md form-input" required>
                        <option value="Veg" <?php echo ($food_preference == 'Veg') ? 'selected' : ''; ?>>Veg</option>
                        <option value="Non Veg" <?php echo ($food_preference == 'Non Veg') ? 'selected' : ''; ?>>Non Veg</option>
                    </select>
                </div>
                <div class="text-lg text-center">
                    Note: Additional payment of 150 for Veg and 200 for Non Veg per person.
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
