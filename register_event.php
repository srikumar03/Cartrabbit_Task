<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CR";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['event_id']) && !empty($_POST['event_id'])) {
        $event_id = $_POST['event_id'];
        $user_id = $_SESSION['user_id']; // Assuming user_id is stored in session

        // Check if the user has already registered for the event
        $check_query = "SELECT * FROM registrations WHERE user_id = $user_id AND event_id = $event_id";
        $check_result = $conn->query($check_query);

        if ($check_result->num_rows > 0) {
            $_SESSION['registration_message'] = "You have already registered for this event.";
        } else {
            // Insert registration into the database
            $insert_query = "INSERT INTO registrations (user_id, event_id) VALUES ($user_id, $event_id)";

            if ($conn->query($insert_query) === TRUE) {
                $_SESSION['registration_message'] = "Successfully registered for the event!";
                $conn->close(); // Close connection before redirection
                header("Location: index.php"); // Redirect back to index.php or any referring page
                exit(); // Ensure no further code execution
            } else {
                $_SESSION['registration_message'] = "Error: " . $conn->error;
            }
        }
    } else {
        $_SESSION['registration_message'] = "Event ID not provided.";
    }
}

$conn->close(); // Close database connection if not already closed
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Status</title>
    <!-- Include any CSS or other meta tags needed -->
</head>

<body>
    <script>
        // Function to show registration message in an alert
        function showRegistrationMessage(message) {
            alert(message);
            // Redirect back to index.php or any referring page after showing the alert
            window.location.href = "index.php";
        }

        // Check if registration message is set in session and show alert
        <?php
        if (isset($_SESSION['registration_message'])) {
            echo "window.onload = function() { showRegistrationMessage('" . $_SESSION['registration_message'] . "'); };";
            unset($_SESSION['registration_message']); // Clear the message after showing
        }
        ?>
    </script>
</body>

</html>