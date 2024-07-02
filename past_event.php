<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "CR";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$current_date = date('Y-m-d');
$query = "SELECT * FROM events WHERE date < '$current_date' OR status = 'completed' ORDER BY date DESC";
$result = $conn->query($query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Past Events</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-3xl mx-auto">
        <h1 class="text-3xl font-bold mb-4">Past Events</h1>

        <?php if ($result->num_rows > 0): ?>
            <ul class="divide-y divide-gray-300">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <li class="py-4">
                        <div class="flex items-center space-x-4">
                            <div>
                                <img src="<?php echo $row['image']; ?>" alt="Event Image"
                                    class="w-16 h-16 object-cover rounded-full">
                            </div>
                            <div>
                                <h2 class="text-xl font-bold"><?php echo $row['title']; ?></h2>
                                <p class="text-gray-600"><?php echo date('M d, Y', strtotime($row['date'])); ?> |
                                    <?php echo $row['location']; ?></p>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No past events.</p>
        <?php endif; ?>
    </div>
</body>

</html>