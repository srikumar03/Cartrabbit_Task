<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CR";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $name, $email, $password);

if ($stmt->execute()) {
    echo "Registration successful!";
    header("Location: login.php");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
exit();
?>