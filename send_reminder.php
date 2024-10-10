<?php
include('db.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Get the logged-in user's ID
$user_id = $_SESSION['user_id'];

// Fetch the logged-in user's email
$user_email_query = "SELECT email FROM users WHERE id = $user_id";
$user_email_result = $conn->query($user_email_query);

if ($user_email_result->num_rows > 0) {
    $user_email_row = $user_email_result->fetch_assoc();
    $user_email = $user_email_row['email'];
} else {
    echo "User email not found.";
    exit();
}

// Fetch upcoming bills for the user
$sql = "SELECT * FROM bills WHERE user_id = $user_id AND due_date >= CURDATE() AND due_date <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)";
$result = $conn->query($sql);

// Initialize the reminder status
$reminder_status = '';

if ($result->num_rows > 0) {
    // Import PHPMailer classes
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    require 'PHPMailer/src/Exception.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Use your mail server
    $mail->SMTPAuth = true;
    $mail->Username = 'Your_email'; // Your email
    $mail->Password = 'your_password'; // Your password or app-specific password
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Set sender and recipient
    $mail->setFrom('Your_email', 'Bill Reminder');
    $mail->addAddress($user_email); // Recipient email

    // Prepare email body with bill reminders
    $mail_body = "Upcoming bills:\n\n";
    while ($row = $result->fetch_assoc()) {
        $mail_body .= "Bill: " . $row['bill_name'] . " - Due Date: " . $row['due_date'] . "\n"."Please pay the upcoming ". $row['bill_name']." bill within the due date!";
    }

    // Set email subject and body
    $mail->Subject = 'Upcoming Bill Payment Reminder';
    $mail->Body = $mail_body;

    // Try to send the email
    if ($mail->send()) {
        $reminder_status = 'success'; // Set success status
    } else {
        $reminder_status = 'error'; // Set error status
    }
} else {
    $reminder_status = 'no_bills'; // Set no upcoming bills status
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Reminder Status</title>
    <link rel="stylesheet" href="style.css"> <!-- External styles -->
    <style>
        /* General Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            transition: opacity 0.4s ease, visibility 0.4s ease;
        }

        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            width: 80%;
            max-width: 500px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            animation: slide-down 0.5s ease-out;
        }

        @keyframes slide-down {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .no-bills {
            background-color: #fff3cd;
            color: #856404;
            border-left: 5px solid #ffc107;
        }

        .close-btn {
            margin-top: 20px;
            padding: 12px 25px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .close-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
        }

        @media screen and (max-width: 600px) {
            .modal-content {
                width: 90%;
                padding: 20px;
            }
            h2 {
                font-size: 22px;
            }
            .close-btn {
                padding: 10px 20px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<!-- Modal for email status -->
<div id="emailStatusModal" class="modal">
    <div class="modal-content <?php echo $reminder_status; ?>">
        <h2>
            <?php
            if ($reminder_status == 'success') {
                echo "Reminder email sent successfully!";
            } elseif ($reminder_status == 'error') {
                echo "Error sending reminder email.";
            } elseif ($reminder_status == 'no_bills') {
                echo "No upcoming bills to send reminders for.";
            }
            ?>
        </h2>
        <button class="close-btn" onclick="closeModal()">Close</button>
    </div>
</div>

<script>
    // Display the modal
    document.getElementById('emailStatusModal').style.display = 'block';

    // Function to close the modal
    function closeModal() {
        document.getElementById('emailStatusModal').style.display = 'none';
        window.location.href = "dashboard.php"; // Redirect to dashboard after closing
    }
</script>

</body>
</html>
