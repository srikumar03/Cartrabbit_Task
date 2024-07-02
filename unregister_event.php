<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CR";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];

    // Delete registration
    $delete_query = "DELETE FROM registrations WHERE event_id = $event_id AND user_id = $user_id";
    if ($conn->query($delete_query) === TRUE) {
        // Redirect back to registered events page
        header("Location: index.php");
        exit();
    } else {
        echo "Error deleting registration: " . $conn->error;
    }
}

$conn->close();
?>