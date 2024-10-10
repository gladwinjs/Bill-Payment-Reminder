db.php   <?php
    $conn = new mysqli('localhost', 'root', '', 'bill_reminder_db');
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
?>