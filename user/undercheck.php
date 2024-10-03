<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Under Verification</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #1a202c;
            color: #fff;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .fade-in {
            animation: fadeIn 2s ease-in;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
        .message-container {
            text-align: center;
        }
        .dot {
            position: absolute;
            width: 8px;
            height: 8px;
            background-color: #fff;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1000;
        }
    </style>
</head>
<body class="fade-in">
    <div class="message-container">
        <h2 class="text-4xl font-bold">Payment Under Verification</h2>
        <p class="text-lg mt-4">Your payment is currently under verification. Please wait...</p>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const body = document.body;

            body.addEventListener("mousemove", function(e) {
                createDots(e);
            });

            function createDots(e) {
                const dot = document.createElement("div");
                dot.classList.add("dot");
                dot.style.left = `${e.pageX}px`;
                dot.style.top = `${e.pageY}px`;
                body.appendChild(dot);

                setTimeout(function() {
                    dot.remove();
                }, 5000);
            }

            // Polling function to check payment status
            function checkPaymentStatus() {
                fetch('check_payment_status.php')
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'verified') {
                            window.location.href = 'application.php';
                        } else {
                            setTimeout(checkPaymentStatus, 5000); // Check every 5 seconds
                        }
                    })
                    .catch(error => console.error('Error checking payment status:', error));
            }

            checkPaymentStatus();
        });
    </script>
</body>
</html>
