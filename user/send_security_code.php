<?php
session_start();
require 'vendor/autoload.php'; // Include Composer's autoloader
include 'header.php';
include 'db_connection.php'; // Include your database connection script

use Mailgun\Mailgun;
use GuzzleHttp\Exception\RequestException;
use Http\Client\Exception\HttpException;

if (!isset($_SESSION['email'])) {
    header("Location: forgot_password.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['email'];
    $security_code = rand(100000, 999999); // Generate a random 6-digit code
    $_SESSION['security_code'] = $security_code;

    // Mailgun API Key and Domain
    $apiKey = 'pubkey-e40bceb2630778518635ff18e30e5f43';
    $domain = 'https://app.mailgun.com/app/sending/domains/sandbox34b63e2b56f242e485caa948a4bb6fb0.mailgun.org'; // e.g., sandbox12345.mailgun.org

    // Create a new Mailgun instance
    $mgClient = Mailgun::create($apiKey);

    $params = [
        'from'    => 'convocationptu@gmail.com',
        'to'      => $email,
        'subject' => 'Your Security Code',
        'text'    => "Your security code is: $security_code"
    ];

    try {
        $response = $mgClient->messages()->send($domain, $params);
        if ($response->getStatusCode() == 200) {
            echo "<script>alert('Security code sent to your email.');</script>";
            header("Location: verify_security_code.php");
            exit();
        } else {
            echo "<script>alert('Failed to send security code. Please try again.');</script>";
        }
    } catch (HttpException $e) {
        echo 'Caught HTTP exception: '. $e->getMessage() ."\n";
        echo '<pre>' . $e->getTraceAsString() . '</pre>'; // Detailed stack trace
    } catch (RequestException $e) {
        echo 'Caught Request exception: '. $e->getMessage() ."\n";
        echo '<pre>' . $e->getTraceAsString() . '</pre>'; // Detailed stack trace
    } catch (Exception $e) {
        echo 'Caught generic exception: '. $e->getMessage() ."\n";
        echo '<pre>' . $e->getTraceAsString() . '</pre>'; // Detailed stack trace
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Security Code</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body>
    <div class="flex items-center justify-center min-h-screen bg-black">
        <div class="w-full max-w-md p-8 space-y-6 bg-gray-900 rounded-lg shadow-lg fade-in">
            <h2 class="text-3xl font-bold text-center text-white">Send Security Code</h2>
            <form class="mt-8 space-y-6" action="" method="POST">
                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Send Security Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php include 'footer.php'; ?>
