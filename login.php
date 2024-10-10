<?php
include('db.php');
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input to prevent SQL injection and XSS attacks
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];  // This line should now properly get the password

    // Query the database to get user info based on the provided email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // Verify password hash stored in the database
        if (password_verify($password, $user['PASSWORD'])) {
            // Correct password; set session and redirect to dashboard
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit();
        } else {
            // Invalid password
            echo "Invalid password!";
        }
    } else {
        // User not found
        echo "No user found with this email!";
    }
}
?>
