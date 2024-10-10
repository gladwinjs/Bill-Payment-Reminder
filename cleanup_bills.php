<?php
include('db.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}

// Get the current date
$current_date = date('Y-m-d');

// SQL query to delete expired bills
$cleanup_sql = "DELETE FROM bills WHERE due_date < '$current_date'";

// Execute the query
if ($conn->query($cleanup_sql) === TRUE) {
    // Optionally set a session variable to notify the user
    $_SESSION['message'] = "Expired bills removed successfully.";
} else {
    $_SESSION['message'] = "Error: " . $conn->error;
}

// Redirect to dashboard after cleanup
header('Location: dashboard.php');
exit();
?>
