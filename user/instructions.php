<?php
include 'headerhome.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['instructions_followed'])) {
    $_SESSION['instructions_followed'] = true;
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Convocation Instructions</title>
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
            <h2 class="text-4xl font-bold text-center">Convocation Instructions</h2>
            <div class="space-y-6">
                <p>37th Convocation of the University is likely to be held in Jan/Feb, 2024. (Keep visiting University’s website regularly for any information/ updates in this regard).</p>
                <p>The students completed their Programme of study in December 2022 and June 2023 Term End Examinations are eligible for award of original certificate at this Convocation.</p>
                <p>The students shall have to attend the Convocation and collect the certificate at his/her Regional Centre where they belong to and not at any other Regional Centre.</p>
                <p>Students will be given option “To Attend Convocation in Person or receive Degree/Diploma by Post”.</p>
                <p>All the students are required to fill their present Address at which they want to receive their Degree/Diploma/Certificate.</p>
                <p>In case, the Convocation is not held at the Regional Centre due to any circumstances where the student belongs to, he/she shall have to attend the Convocation at other nearby Regional Centre which may be mostly in the same state/nearby state. Otherwise his/her Degree/Diploma/Certificate will be sent to his/her address by post. Intimation in this regard will be issued later on.</p>
                <p>In case, the students are not invited to attend the Convocation or could not attend the Convocation even after submission of the requisite fee, their degree/diploma will be sent by post by the concerned Regional Centre after the convocation and in case of P.G. Certificate/Certificate Programmes, Certificate will be sent by Headquarters. Such students should therefore contact their concerned Regional Centre, where they belong to/Headquarters, as the case may be.</p>
                <h3 class="text-2xl font-bold">Instructions for the Students:</h3>
                <ul class="list-disc list-inside space-y-2">
                    <li>Please register below for "On-line Registration for 37th Convocation".</li>
                    <li>Eligible students are required to register themselves for obtaining the Degree/Diploma/ Certificate and pay the requisite fee through Online mode only as per the details below:</li>
                    <ul class="list-disc list-inside ml-8 space-y-1">
                        <li>Ph.D/ M.Phil/ Master Degree/Bachelor Degree/ PG Diploma/ Diploma programmes = Rs.600/-</li>
                        <li>PG Certificate/ Certificate programmes = Rs. 200/-</li>
                    </ul>
                    <li>The requisite fee can be paid online through the following modes:</li>
                    <ul class="list-disc list-inside ml-8 space-y-1">
                        <li>Debit/ Credit Card (Master/Visa/Rupay) of any bank.</li>
                        <li>Net Banking</li>
                    </ul>
                    <li><a href="#" class="text-indigo-600 hover:text-indigo-500">Click Here</a> to print the acknowledgement. If you do not receive an acknowledgement, you may wait for 48 hours.</li>
                    <li>For discrepancy, if any, in payment of the Convocation fee, contact at the following number and E-mail: 011-29572209 [ convocation_feequery@ptu.ac.in ]</li>
                    <li>For general query on the Convocation, students may contact at the following number 011-29572224 and E-mail:- convocation@ptu.ac.in</li>
                    <li>PS: Students of MBA (Students registered up to July, 2017 batch), MCA and M.COM, etc. are required to remit registration fee @Rs.600/- per certificate. Students have to take all the certificates, if they have qualified for more than one module of the Programme paying Rs.600/- each.</li>
                </ul>
                <h3 class="text-2xl font-bold">Declaration</h3>
                <p>I have read all the instructions mentioned above and certify that I have completed the prescribed course of study and passed all the examinations, and I have been awarded a provisional degree/diploma/certificate. As I have not received the same Original Degree till date, I want to apply to issue the original certificate.</p>
                <form method="POST" id="declaration-form">
                    <div class="flex items-center">
                        <input id="declaration-checkbox" name="instructions_followed" type="checkbox" value="true" class="mr-2 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        <label for="declaration-checkbox" class="text-lg ml-2 block text-sm text-red-600">I agree to the declaration</label>
                    </div>
                    <div class="flex justify-center mt-4">
                        <button id="agree-button" type="submit" class="py-3 px-6 border border-transparent text-lg font-medium rounded-md text-indigo-700 bg-indigo-200 hover:bg-indigo-700 hover:text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" disabled>Agree and Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('declaration-checkbox').addEventListener('change', function() {
            document.getElementById('agree-button').disabled = !this.checked;
        });
    </script>
</body>
</html>

<?php
include 'footer.php';
?>
