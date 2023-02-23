<?php
$conn = new mysqli('localhost', 'root', '', 'database_name');

if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}

$username = $_POST['username'];
$password = $_POST['password'];
$account_type = $_POST['account_type'];

// $hashed_password = password_hash($password, PASSWORD_DEFAULT);
$encryption_key = 'aleksa'; // Replace with your own secret encryption key

$options = [
    'salt' => $encryption_key, // Use the encryption key as the salt
    'cost' => 12, // Choose a suitable cost factor
];

$hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);

$sql = "INSERT INTO users (username, password, type) VALUES ('$username', '$hashed_password', '$account_type')";
$result = mysqli_query($conn, $sql);

if ($result) {
  // Registration successful - redirect to login page or other destination
  echo "Uspen si se registrovao!";
  exit();
} else {
  // Registration failed - display error message
  echo "Registration failed. Please try again.";
}

mysqli_close($conn);
?>
