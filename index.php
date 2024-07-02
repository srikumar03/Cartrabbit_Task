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
$is_logged_in = isset($_SESSION['user_id']);

// Function to check if user is registered for an event
function isRegistered($conn, $user_id, $event_id)
{
    $check_query = "SELECT * FROM registrations WHERE event_id = $event_id AND user_id = $user_id";
    $result = $conn->query($check_query);
    return $result->num_rows > 0;
}

// Handle registration and unregistration actions
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $event_id = $_POST['event_id'];
    $action = $_POST['action'];

    if ($action == 'register') {
        // Perform registration logic (insert into registrations table)
        $register_query = "INSERT INTO registrations (event_id, user_id) VALUES ($event_id, $user_id)";
        if ($conn->query($register_query) === TRUE) {
            // echo '<script>alert("Successfully registered for the event!");</script>';
        } else {
            echo "Error: " . $conn->error;
        }
    } elseif ($action == 'unregister') {
        // Perform unregistration logic (delete from registrations table)
        $unregister_query = "DELETE FROM registrations WHERE event_id = $event_id AND user_id = $user_id";
        if ($conn->query($unregister_query) === TRUE) {
            // echo '<script>alert("Successfully unregistered from the event!");</script>';
        } else {
            echo "Error: " . $conn->error;
        }
    }
}

// Fetch upcoming events
$upcoming_events_query = "SELECT * FROM events WHERE date >= CURDATE() ORDER BY date ASC";
$upcoming_events_result = $conn->query($upcoming_events_query);

// Fetch past events
$past_events_query = "SELECT * FROM events WHERE status='completed' OR date < CURDATE() ORDER BY date DESC";
$past_events_result = $conn->query($past_events_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        /* Glassmorphism Effect */
        .glassmorphism {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 10px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            transition: all 0.3s ease;
        }

        .glassmorphism:hover {
            /* transform: scale(1.02); */
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>

<body class="bg-gradient-to-r from-gray-200 to-gray-300 min-h-screen flex items-center justify-center">
    <div class="container mx-auto p-6">
        <?php if ($is_logged_in): ?>
            <h1 class="text-3xl font-bold mb-4 text-center">Hello, <span
                    class="text-amber-900"><?php echo $_SESSION['user_name']; ?>!</span></h1>
            <div class="flex justify-center space-x-4 mb-4">
                <a href="logout.php"
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300">Logout</a>
            </div>
        <?php else: ?>
            <div class="flex justify-center space-x-4 mb-4">
                <a href="login.php"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">Login</a>
                <a href="register.php"
                    class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">Register</a>
            </div>
        <?php endif; ?>

        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4 text-center">Upcoming Events</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($upcoming_events_result->num_rows > 0): ?>
                    <?php while ($event = $upcoming_events_result->fetch_assoc()): ?>
                        <div class="glassmorphism bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition duration-300">
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($event['date']); ?></p>
                            <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($event['location']); ?></p>
                            <?php if (!empty($event['image'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image"
                                    class="mb-2 rounded-lg">
                            <?php endif; ?>

                            <!-- Display registered users if current user is event poster -->
                            <?php if ($event['user_id'] == $user_id): ?>
                                <div class="mt-4">
                                    <h4 class="text-lg font-bold mb-2">Registered Users:</h4>
                                    <?php
                                    $registered_users_query = "SELECT users.name, users.email FROM registrations
                                                          JOIN users ON registrations.user_id = users.id
                                                          WHERE registrations.event_id = {$event['id']}";
                                    $registered_users_result = $conn->query($registered_users_query);

                                    if ($registered_users_result->num_rows > 0) {
                                        while ($user = $registered_users_result->fetch_assoc()) {
                                            echo "<p>Name: " . htmlspecialchars($user['name']) . " | Email: " . htmlspecialchars($user['email']) . "</p>";
                                        }
                                    } else {
                                        echo "<p>No registered users.</p>";
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($is_logged_in): ?>
                                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                                    <?php if (isRegistered($conn, $user_id, $event['id'])): ?>
                                        <button type="submit" name="action" value="unregister"
                                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300">Unregister</button>
                                    <?php else: ?>
                                        <button type="submit" name="action" value="register"
                                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-300">Register</button>
                                    <?php endif; ?>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">No upcoming events.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4 text-center">Past Events</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if ($past_events_result->num_rows > 0): ?>
                    <?php while ($event = $past_events_result->fetch_assoc()): ?>
                        <div class="glassmorphism bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition duration-300">
                            <h3 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($event['title']); ?></h3>
                            <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($event['date']); ?></p>
                            <p class="text-gray-700 mb-2"><?php echo htmlspecialchars($event['location']); ?></p>
                            <?php if (!empty($event['image'])): ?>
                                <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="Event Image"
                                    class="mb-2 rounded-lg">
                            <?php endif; ?>
                            <?php if ($is_logged_in && $event['user_id'] == $user_id): ?>
                                <form action="delete_event.php" method="post">
                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                    <button type="submit"
                                        class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded transition duration-300">Delete</button>
                                </form>
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>"
                                    class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded mt-2 transition duration-300">Edit</a>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">No past events.</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($is_logged_in): ?>
            <div class="mt-8">
                <h2 class="text-2xl font-bold mb-4 text-center">Add New Event</h2>
                <form action="add_event.php" method="post" enctype="multipart/form-data"
                    class="glassmorphism bg-white p-4 rounded-lg shadow-md hover:shadow-xl transition duration-300">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">Event Title</label>
                        <input type="text" name="title" id="title" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="date">Event Date</label>
                        <input type="date" name="date" id="date" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="location">Event Location</label>
                        <input type="text" name="location" id="location" required
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="image">Event Image</label>
                        <input type="file" name="image" id="image"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <button type="submit"
                        class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded transition duration-300">Add
                        Event</button>
                </form>
            </div>
        <?php endif; ?>

    </div>
</body>

</html>