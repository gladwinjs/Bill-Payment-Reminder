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

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $bill_name = $conn->real_escape_string($_POST['bill_name']);
    $amount = $_POST['amount'];
    $due_date = $_POST['due_date'];

    // Insert the new bill into the database
    $sql = "INSERT INTO bills (user_id, bill_name, amount, due_date, status) 
            VALUES ('$user_id', '$bill_name', '$amount', '$due_date', 'Pending')";

    if ($conn->query($sql) === TRUE) {
        // Redirect back to the dashboard after successfully adding the bill
        header('Location: dashboard.php');
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
