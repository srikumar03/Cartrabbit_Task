<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "CR";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $title = $_POST['title'];
    $date = $_POST['date'];
    $location = $_POST['location'];
    $user_id = $_SESSION['user_id'];
    $status = 'pending'; // Assuming new events are always pending initially

    // Handle image upload if provided
    $image = ''; // default empty string
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        $image = $target_file;
    }

    $stmt = $conn->prepare("INSERT INTO events (title, date, location, user_id, status, image) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssiis", $title, $date, $location, $user_id, $status, $image);

    if ($stmt->execute()) {
        header("Location: index.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .form-control {
            width: 300px;
            max-width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
    </style>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-4 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-4 text-center">Add Event</h1>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post"
            enctype="multipart/form-data">
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Event Title</label>
                <input type="text" id="title" name="title" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label for="date" class="block text-sm font-medium text-gray-700">Event Date</label>
                <input type="date" id="date" name="date" class="form-control" required>
            </div>

            <div class="mb-4">
                <label for="location" class="block text-sm font-medium text-gray-700">Event Location</label>
                <input type="text" id="location" name="location" class="form-control" required>
            </div>

            <div class="mb-4">
                <label for="image" class="block text-sm font-medium text-gray-700">Event Image</label>
                <input type="file" id="image" name="image" class="form-control">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Add
                    Event</button>
            </div>
        </form>
    </div>
</body>

</html>