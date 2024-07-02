<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "CR";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($id, $name, $hashed_password);

    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;  // Optional: Store user's name in session
        header("Location: index.php");
        exit();
    } else {
        $login_error = "Invalid email or password.";
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
    <title>Login</title>
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

        .error-message {
            color: red;
            font-size: 14px;
        }
    </style>
</head>

<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white p-4 rounded-lg shadow-lg">
        <h1 class="text-2xl font-bold mb-4 text-center">Login</h1>

        <?php if (isset($login_error)): ?>
            <p class="error-message"><?php echo $login_error; ?></p>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <input type="email" id="email" name="email" class="form-control" required autofocus>
            </div>

            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit"
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">Login</button>
            </div>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">Don't have an account? <a href="register.php"
                class="text-blue-600">Register here</a></p>
    </div>
</body>

</html>