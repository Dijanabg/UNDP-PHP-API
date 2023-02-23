<?php
// a' OR '1'='1
$conn = new mysqli('localhost', 'root', '', 'database_name');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// $username = $_POST['username'];
// $password = $_POST['password'];
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

$sql = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
$result = $conn->query($sql);

if (mysqli_num_rows($result) > 0) {
    // User is authenticated - redirect to dashboard or other protected page
    var_dump($result->fetch_assoc());
    var_dump("Zdravo {$result->fetch_assoc()['type']}");
    exit();
} else {
    // User is not authenticated - display error message
    echo "Invalid username or password.";
}

mysqli_close($conn);