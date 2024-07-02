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
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $date = $_POST['date'];
    $location = $_POST['location'];

    // Check if a new image file is uploaded
    if ($_FILES['image']['size'] > 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file size
        if ($_FILES["image"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (
            $imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
            && $imageFileType != "gif"
        ) {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                echo "The file " . htmlspecialchars(basename($_FILES["image"]["name"])) . " has been uploaded.";
            } else {
                echo "Sorry, there was an error uploading your file.";
            }
        }

        $image = $target_file; // Update $image with the new file path
    } else {
        // No new image uploaded, retain the existing image path
        $image = $_POST['current_image']; // Assuming you have a hidden input field for current image
    }

    // Update event in database
    $update_query = "UPDATE events SET title=?, date=?, location=?, image=? WHERE id=?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssssi", $title, $date, $location, $image, $event_id);

    if ($stmt->execute()) {
        // Redirect to index.php after successful update
        header("Location: index.php");
        exit();
    } else {
        echo "Error updating event: " . $conn->error;
    }
}

// Fetch event details to pre-populate the form
if (isset($_GET['id'])) {
    $event_id = $_GET['id'];
    $select_query = "SELECT * FROM events WHERE id=?";
    $stmt = $conn->prepare($select_query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $event = $result->fetch_assoc();
    } else {
        echo "Event not found.";
        exit();
    }
} else {
    echo "Event ID not provided.";
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-8">
    <h1 class="text-3xl font-bold mb-4">Edit Event</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data"
        class="bg-white p-4 rounded-lg shadow">
        <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
        <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($event['image']); ?>">
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="title">Event Title</label>
            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($event['title']); ?>" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Event Date</label>
            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($event['date']); ?>" required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="location">Event Location</label>
            <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($event['location']); ?>"
                required
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Event Image</label>
            <input type="file" name="image" id="image"
                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
        </div>
        <?php if ($event['image']): ?>
            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Current Event Image" class="mb-2">
        <?php endif; ?>
        <div class="flex items-center justify-between">
            <button type="submit"
                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Update
                Event</button>
        </div>
    </form>
</body>

</html>