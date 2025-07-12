<?php
session_start();

// Database connection
$host = "localhost";
$username = "root";
$password = "";
$database = "bank";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
            $error = "Incorrect password.";
        }
    } else {
        $error = "Account not found.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign In</title>
    <link rel="stylesheet" href="signin.css">
</head>
<body>
    <div class="split-container">
        <div class="info-panel">
            <h2>Welcome Back!</h2>
            <p>Login to access your PiggyBank Online account.</p>
            <a href="register.php" class="btn-light">Create Account</a>
        </div>
        <div class="form-panel">
            <h2>Sign In</h2>
            <p>Enter your account number and password.</p>
            <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
            <form method="POST" action="">
                <label>Account Number</label>
                <input type="number" name="account_number" required>

                <label>Password</label>
                <input type="password" name="password" required>

                <input type="submit" value="Log In" class="btn">
            </form>
        </div>
    </div>
</body>
</html>
