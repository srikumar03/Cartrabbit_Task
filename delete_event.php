<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CR";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_POST['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("DELETE FROM events WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $user_id);

if ($stmt->execute()) {
    echo "Event deleted successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();

header("Location: index.php");
exit();
?>