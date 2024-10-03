<?php
require_once('vendor/autoload.php'); // Include TCPDF library

session_start();

$jsonFilePath = 'registration_details.json';

// Check if registration details are in session (using either reg_no or email)
if (isset($_SESSION['reg_no'])) {
    $identifier = $_SESSION['reg_no']; // Assuming reg_no is stored in session
} elseif (isset($_SESSION['email'])) {
    $identifier = $_SESSION['email']; // Assuming email is stored in session
} else {
    die('Session data not found.');
}

// Read JSON file and find application details
if (file_exists($jsonFilePath)) {
    $jsonContent = file_get_contents($jsonFilePath);
    $registrations = json_decode($jsonContent, true);

    $ticketDetails = null;

    // Search for the ticket details using the identifier (reg_no or email) in session
    foreach ($registrations as $reg_no => $details) {
        if (($details['reg_no'] === $identifier || $details['email'] === $identifier) && isset($details['seat_number']) && isset($details['token_number'])) {
            $ticketDetails = $details;
            break;
        }
    }

    if ($ticketDetails) {
        // Create new PDF document
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Your Name');
        $pdf->SetTitle('Convocation Ticket');
        $pdf->SetSubject('Convocation Ticket');
        $pdf->SetKeywords('Convocation, Ticket');

        // Add a page
        $pdf->AddPage();

        // Set background image (adjust path as per your setup)
        $backgroundImage = 'path/to/your/background/image.jpg'; // Replace with your image path
        $pdf->Image($backgroundImage, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);

        // Header
        $pdf->SetFillColor(128, 0, 0); // Maroon color
        $pdf->Rect(0, 0, 210, 30, 'F'); // Rectangle for header
        $pdf->SetTextColor(255, 255, 255); // White color for header text
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(10, 8); // Position for university name
        $pdf->Cell(190, 14, 'PUDUCHERRY TECHNOLOGICAL UNIVERSITY', 0, 1, 'C', 0);

        // Title
        $pdf->SetTextColor(0, 0, 0); // Black color for title text
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Ln(20); // Line break
        $pdf->Cell(0, 10, 'Convocation Ticket', 0, 1, 'C');
        $pdf->Ln(20);
        // Banner with Ticket Number
        $pdf->SetTextColor(0,0,0); // White color for text
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(140, 20); // Adjust position as needed
        $pdf->Cell(60, 10, 'Ticket Number: ' . htmlspecialchars($ticketDetails['token_number']), 0, 1, 'R');

        // Content
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        $pdf->Ln(30); // Line break
        $content = "
            <strong>Name:</strong> " . htmlspecialchars($ticketDetails['name']) . "<br>
            <strong>Registration Number:</strong> " . htmlspecialchars($ticketDetails['reg_no']) . "<br>
            <strong>Degree Name:</strong> " . htmlspecialchars($ticketDetails['degree_name']) . "<br>
            <strong>Passout Year:</strong> " . htmlspecialchars($ticketDetails['passout_year']) . "<br>
            <strong>Mode of Collection:</strong> " . htmlspecialchars($ticketDetails['mode_of_collection']) . "<br>";

        if ($ticketDetails['mode_of_collection'] === 'In Person') {
            $content .= "
                <strong>Number of Accompanying Persons:</strong> " . htmlspecialchars($ticketDetails['accompanying_persons']) . "<br>
                <strong>Food Preference:</strong> " . htmlspecialchars($ticketDetails['food_preference']) . "<br>";
        }

        $content .= "
            <strong>Email:</strong> " . htmlspecialchars($ticketDetails['email']) . "<br>
            <strong>Total Cost:</strong> " . htmlspecialchars($ticketDetails['total_cost']) . "
        ";
        $pdf->writeHTML($content, true, false, true, false, '');

        // Footer
        $pdf->SetY(280); // Adjust the Y position as needed
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Dr. Anbarasi', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'Incharge of Convocation', 0, 1, 'R');
        // Output the PDF as a download
        $pdf->Output('convocation_ticket $reg_no.pdf', 'D');

        // End script execution
        exit();
    } else {
        die('Ticket details not found.');
    }
} else {
    die('Registration details JSON file not found.');
}
?>
