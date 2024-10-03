<?php
require_once('vendor/autoload.php'); // Include TCPDF library

session_start();

$jsonFilePath = 'registration_details.json';

// Check if registration details are in session
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

    $details = null;

    // Search for the identifier in the JSON data
    foreach ($registrations as $reg) {
        if ($reg['reg_no'] === $identifier || $reg['email'] === $identifier) {
            $details = $reg;
            break;
        }
    }

    if ($details) {
        // Create new PDF document
        $pdf = new TCPDF();
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Puducherry Technological University');
        $pdf->SetTitle($details['reg_no']); // Set PDF title with registration number
        $pdf->SetSubject('Application Details');
        $pdf->SetKeywords('Application, Details');

        // Add a page
        $pdf->AddPage();

        // Set background image (college logo) with low opacity
        $backgroundImage = 'C:\xampp\htdocs\acrs\logobg.png'; // Adjust path as per your system
        $pdf->Image($backgroundImage, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 50);

        // Set header
        $pdf->SetFillColor(128, 0, 0); // Maroon color
        $pdf->Rect(0, 0, 210, 30, 'F'); // Rectangle for header
        $pdf->SetTextColor(255, 255, 255); // White color for header text
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(10, 8); // Position for university name
        $pdf->Cell(190, 14, 'PUDUCHERRY TECHNOLOGICAL UNIVERSITY', 0, 1, 'C', 0);

        // Set title
        $pdf->SetTextColor(0, 0, 0); // Black color for title text
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Ln(20); // Line break
        $pdf->Cell(0, 10, 'Convocation Registration - Application Details', 0, 1, 'C');

        // Set content
        $pdf->SetFont('helvetica', '', 14);
        $pdf->SetLeftMargin(20);
        $pdf->SetRightMargin(20);
        $pdf->Ln(10); // Line break
        $content = "
            <strong>Name:</strong> " . htmlspecialchars($details['name']) . "<br>
            <strong>Registration Number:</strong> " . htmlspecialchars($details['reg_no']) . "<br>
            <strong>Degree Name:</strong> " . htmlspecialchars($details['degree_name']) . "<br>
            <strong>Passout Year:</strong> " . htmlspecialchars($details['passout_year']) . "<br>
            <strong>Mode of Collection:</strong> " . htmlspecialchars($details['mode_of_collection']) . "<br>";

        if ($details['mode_of_collection'] === 'In Person') {
            $content .= "
                <strong>Number of Accompanying Persons:</strong> " . htmlspecialchars($details['accompanying_persons']) . "<br>
                <strong>Food Preference:</strong> " . htmlspecialchars($details['food_preference']) . "<br>";
        } elseif ($details['mode_of_collection'] === 'by post') {
            $content .= "
                <strong>Address for Sending:</strong> " . htmlspecialchars($details['address_for_sending']) . "<br>";
        }

        $content .= "
            <strong>Email:</strong> " . htmlspecialchars($details['email']) . "<br>
            <strong>Total Cost:</strong> " . htmlspecialchars($details['total_cost']) . "
        ";
        $pdf->writeHTML($content, true, false, true, false, '');

        // Add signature and name in footer
        $signatureImage = 'C:\xampp\htdocs\acrs\signature.png'; // Path to the signature image
        $pdf->Image($signatureImage, 150, 240, 40, 20, '', '', '', false, 300, '', false, false, 0);
        $pdf->SetY(-30);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Dr. Anbarasi', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 10);
        $pdf->Cell(0, 10, 'Incharge of Convocation', 0, 1, 'R');

        // Output the PDF as a download
        $pdf->Output($details['reg_no'] . '.pdf', 'D');
        exit();
    } else {
        die('Application details not found.');
    }
} else {
    die('Registration details JSON file not found.');
}
?>
