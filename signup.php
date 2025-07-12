<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit();
}

// DB connection
$host = "localhost";
$username = "root";
$password = "";
$database = "bank";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Login logic
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $account_number = $_POST['account_number'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_number);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = "❌ Incorrect password.";
        }
    } else {
        $error = "❌ Account not found.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - PiggyBank</title>
    <link rel="stylesheet" href="signup.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h2 class="form-title">PiggyBank Login</h2>

    <?php if (!empty($error)): ?>
        <p style="color: red; text-align:center;"><?= $error ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="input-group">
            <i class='bx bx-user'></i>
            <input type="number" name="account_number" id="account_number" placeholder=" " required>
            <label for="account_number">Account Number</label>
        </div>

        <div class="input-group">
            <i class='bx bx-lock'></i>
            <input type="password" name="password" id="password" placeholder=" " required>
            <label for="password">Password</label>
        </div>

        <div class="recover"><a href="#">Forgot Password?</a></div>

        <input type="submit" value="Login" class="btn">

        <div class="or">Don't have an account? <a href="register.php">Register here</a></div>
    </form>
</div>
</body>
</html>
