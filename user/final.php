<?php
include 'db_connection.php';
session_start();

// Function to read JSON file and return decoded data
function readJsonFile($filename) {
    $jsonString = file_get_contents($filename);
    return json_decode($jsonString, true);
}

// Path to the registration details JSON file
$jsonFilePath = 'registration_details.json';

// Check if JSON file exists
if (!file_exists($jsonFilePath)) {
    die("Error: registration_details.json not found.");
}

// Read JSON data
$registrationData = readJsonFile($jsonFilePath);

// Initialize variables
$reg_no = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
$name = '';
$degree_name = '';
$passout_year = '';
$mode_of_collection = '';
$accompanying_persons = 0;
$food_preference = '';
$address_for_sending = '';

// Check if registration data exists for the given reg_no
if (isset($registrationData[$reg_no])) {
    $data = $registrationData[$reg_no];
    $passout_year = $data['passout_year'];
    $mode_of_collection = $data['mode_of_collection'];
    $name = $data['name'];
    $degree_name = $data['degree_name'];
    
    if ($mode_of_collection == 'In Person') {
        $accompanying_persons = $data['accompanying_persons'];
        $food_preference = $data['food_preference'];
    } elseif ($mode_of_collection == 'by post') {
        $address_for_sending = $data['address_for_sending'];
    }
}

// Check if POST request to save data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($_POST['action'] == 'edit') {
        // Redirect or perform any edit actions
        // For example, redirect to an edit page
        header('Location: form.php');
        exit;
    } elseif ($_POST['action'] == 'save_to_db') {
        echo "<script>
                if (confirm('Data cannot be changed. Do you want to continue?')) {
                    window.location.href = 'payment.php';
                }
              </script>";
        $sql_registration = "INSERT INTO Registration (Reg_no, Passout_year, Mode_of_collection) 
                             VALUES (?, ?, ?) 
                             ON DUPLICATE KEY UPDATE 
                             Passout_year = VALUES(Passout_year), 
                             Mode_of_collection = VALUES(Mode_of_collection)";
        $stmt = $conn->prepare($sql_registration);
        $stmt->bind_param("sis", $reg_no, $passout_year, $mode_of_collection);
        $stmt->execute();
        $stmt->close();

        // Insert/update logic for InPersonCollection or ByPost table based on mode_of_collection
        if ($mode_of_collection == 'In Person') {
            $sql_in_person = "INSERT INTO InPersonCollection (Reg_no, Accompanying_persons, Food_preference) 
                              VALUES (?, ?, ?) 
                              ON DUPLICATE KEY UPDATE 
                              Accompanying_persons = VALUES(Accompanying_persons), 
                              Food_preference = VALUES(Food_preference)";
            $stmt = $conn->prepare($sql_in_person);
            $stmt->bind_param("sis", $reg_no, $accompanying_persons, $food_preference);
            $stmt->execute();
            $stmt->close();
        } elseif ($mode_of_collection == 'by post') {
            $sql_by_post = "INSERT INTO ByPost (Reg_no, Address_for_sending) 
                            VALUES (?, ?) 
                            ON DUPLICATE KEY UPDATE 
                            Address_for_sending = VALUES(Address_for_sending)";
            $stmt = $conn->prepare($sql_by_post);
            $stmt->bind_param("ss", $reg_no, $address_for_sending);
            $stmt->execute();
            $stmt->close();
        }

        // Close database connection
        // $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Details</title>
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
        .readonly {
            background-color: #edf2f7;
            pointer-events: none;
        }
    </style>
</head>
<body class="fade-in">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-black text-white">
        <div class="max-w-md w-full space-y-8 form-container">
            <h2 class="text-4xl font-bold text-center">Final Details</h2>
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
                    <select id="mode_of_collection" name="mode_of_collection" class="p-2 rounded-md form-input" required disabled>
                        <option value="In Person" <?php echo ($mode_of_collection == 'In Person') ? 'selected' : ''; ?>>In Person</option>
                        <option value="By Post" <?php echo ($mode_of_collection == 'by post') ? 'selected' : ''; ?>>By Post</option>
                    </select>
                </div>
                <div id="in_person_fields" class="space-y-6" style="display: <?php echo ($mode_of_collection == 'In Person') ? 'block' : 'none'; ?>">
                    <div class="flex flex-col">
                        <label for="accompanying_persons" class="text-lg form-label">Number of Accompanying Persons</label>
                        <input type="number" id="accompanying_persons" name="accompanying_persons" class="p-2 rounded-md form-input" value="<?php echo htmlspecialchars($accompanying_persons); ?>">
                    </div>
                    <div class="flex flex-col">
                        <label for="food_preference" class="text-lg form-label">Food Preference</label>
                        <input type="text" id="food_preference" name="food_preference" value="<?php echo htmlspecialchars($food_preference); ?>" readonly class="p-2 rounded-md form-input readonly">
                    </div>
                    <div class="text-lg text-center">
                        Note: Additional payment of 150 for Veg and 200 for Non Veg per person.
                    </div>
                </div>
                
                <div id="by_post_fields" class="flex flex-col" style="display: <?php echo
($mode_of_collection == 'by post') ? 'block' : 'none'; ?>">
<label for="address_for_sending" class="text-lg form-label">Address for Sending</label>
<textarea id="address_for_sending" name="address_for_sending" class="p-2 rounded-md form-input"><?php echo htmlspecialchars($address_for_sending); ?></textarea>
</div>            <div class="flex space-x-4">
                <button type="submit" name="action" value="edit" class="group relative flex-1 justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Edit
                </button>
                <button type="submit" name="action" value="save_to_db" class="group relative flex-1 justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-500 hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Save and Proceed
                </button>
            </div>
        </form>
    </div>
</div>
