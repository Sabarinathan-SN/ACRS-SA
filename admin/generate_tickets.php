<?php
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_GET['studentSeats']) || !isset($_GET['staffSeats'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect.php';
require 'C:\xampp\htdocs\acrs\vendor\autoload.php'; // Include Composer's autoloader or manually include PHPMailer files

$admin_id = $_SESSION['admin_id'];
$studentSeats = intval($_GET['studentSeats']);
$staffSeats = intval($_GET['staffSeats']);

// Fetch the admin's role from the database
$role_sql = "SELECT r.role_id FROM Admin a JOIN roles r ON a.role_id = r.role_id WHERE a.admin_id = ?";
$stmt = $conn->prepare($role_sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($role_id);
$stmt->fetch();
$stmt->close();

if ($role_id != 1) {
    header("Location: admin_dashboard.php");
    exit();
}

// Fetch students from inpersoncollection ordered by Dpt_id then by the last two digits of Reg_no
$fetch_students_sql = "
   SELECT s.Reg_no, s.Name, d.Dpt_name 
FROM inpersoncollection i 
JOIN Student s ON i.Reg_no = s.Reg_no 
JOIN Department d ON s.Dpt_id = d.Dpt_id 
JOIN Registration r ON s.Reg_no = r.Reg_no 
WHERE r.Payment_status = 'verified' 
ORDER BY s.Dpt_id, RIGHT(s.Reg_no, 2)";

$students_result = $conn->query($fetch_students_sql);

if ($students_result->num_rows > 0) {
    $seat_no = 1;
    $token_number = 1;
    $confirmed_reg_nos = [];
    $conn->autocommit(FALSE);

    while ($row = $students_result->fetch_assoc()) {
        if ($seat_no > $studentSeats) {
            break;
        }

        $insert_ticket_sql = "
            INSERT INTO ticket (Seat_id, Reg_no, Token_number) 
            VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_ticket_sql);
        $stmt->bind_param("iss", $seat_no, $row['Reg_no'], str_pad($token_number, 4, '0', STR_PAD_LEFT));
        $stmt->execute();
        $stmt->close();

        // Update registration_details.json with ticket and token numbers
        $jsonFilePath = 'C:\xampp\htdocs\acrs\registration_details.json';
        if (file_exists($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $registrations = json_decode($jsonContent, true);

            // Update the specific registration if exists
            if (isset($registrations[$row['Reg_no']])) {
                $registrations[$row['Reg_no']]['seat_number'] = $seat_no;
                $registrations[$row['Reg_no']]['token_number'] = str_pad($token_number, 4, '0', STR_PAD_LEFT);
            }

            // Save updated data back to the JSON file
            file_put_contents($jsonFilePath, json_encode($registrations, JSON_PRETTY_PRINT));
        }

        // Add confirmed registration number to the list
        $confirmed_reg_nos[] = $row['Reg_no'];

        // Fetch email from t_reg table
        $email_sql = "SELECT email FROM t_reg WHERE Reg_no = ?";
        $stmt = $conn->prepare($email_sql);
        $stmt->bind_param("s", $row['Reg_no']);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();

        // Send email using PHPMailer
        $mail = new PHPMailer\PHPMailer\PHPMailer();
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Set your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'convocationptu@gmail.com'; // SMTP username
        $mail->Password = 'ylzu ulym bnly lljv'; // SMTP password or App Password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('convocationptu@gmail.com', 'Convocation - Tickets');
        $mail->addAddress($email); // Add the student's email address

        $mail->isHTML(true);
        $mail->Subject = 'Your Seat and Token Information';
        $mail->Body = "Dear " . htmlspecialchars($row['Name']) . ",<br>Your seat number is: " . htmlspecialchars($seat_no) . "<br>Your token number is: " . htmlspecialchars(str_pad($token_number, 4, '0', STR_PAD_LEFT)) . "<br>Thank you.";

        if (!$mail->send()) {
            echo "Mailer Error: " . $mail->ErrorInfo;
        } else {
            echo "Message sent to: " . htmlspecialchars($email) . "<br>";
        }

        $seat_no++;
        $token_number++;
    }

    if ($conn->commit()) {
        // Calculate remaining seats
        $remaining_seats = $studentSeats - ($seat_no - 1);

        // Store remaining seats in JSON file
        $rem_seats_data = ['remaining_seats' => $remaining_seats];
        file_put_contents('rem_seats.json', json_encode($rem_seats_data));

        // Update registration status to closed
        $reg_status_file = 'reg_status.json';
        if (file_exists($reg_status_file)) {
            $reg_status = json_decode(file_get_contents($reg_status_file), true);
        } else {
            $reg_status = [];
        }

        $reg_status['status'] = 'closed';
        file_put_contents($reg_status_file, json_encode($reg_status));

        // Update status.json file
        $status_file = 'C:/xampp/htdocs/acrs/status.json';
        if (file_exists($status_file)) {
            $status_data = json_decode(file_get_contents($status_file), true);
        
            // Iterate over all entries in status_data
            foreach ($status_data as $email => $details) {
                if (in_array($details['reg_no'], $confirmed_reg_nos)) {
                    // Update status to "ticket.php" if registration number is confirmed
                    $status_data[$email]['status'] = 'ticket.php';
                } else {
                    // Update status to "waitlist.php" if registration number is not confirmed
                    $status_data[$email]['status'] = 'waitlist.php';
                }
            }

            // Save the updated status data back to the JSON file
            file_put_contents($status_file, json_encode($status_data, JSON_PRETTY_PRINT));
        }

        // Redirect to display_tickets.php
        header("Location: display_tickets.php");
        exit();
    } else {
        $conn->rollback();
        echo "Error: " . $conn->error;
    }
} else {
    echo "No students found";
}

$conn->close();
?>
