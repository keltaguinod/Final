<?php
session_start();

//check if logged in
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

//connect to database
$host = "localhost";
$username = "root";
$password = "";
$database = "bank";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user']['user_id'];

//user data
$sql = "SELECT * FROM user WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
} else {
    echo "<p>User not found.</p>";
    exit();
}

$stmt->close();

//password changer
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_new_password = $_POST['confirm_new_password'];

    if (!password_verify($current_password, $user['password'])) {
        $password_message = "<p style='color:red;'>Current password is incorrect.</p>";
    } elseif ($new_password !== $confirm_new_password) {
        $password_message = "<p style='color:red;'>New passwords do not match.</p>";
    } else {
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

        $update_sql = "UPDATE user SET password = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_new_password, $user_id);

        if ($update_stmt->execute()) {
            $password_message = "<p style='color:green;'>Password updated successfully.</p>";
        } else {
            $password_message = "<p style='color:red;'>Error updating password.</p>";
        }

        $update_stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Personal Information</title>
    <link rel="stylesheet" href="personalinfo.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <h2>PiggyBank</h2>
        <nav>
            <ul>
                <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
                <li class="active"><a href="personalinfo.php"><i class='bx bxs-user'></i>Personal Information</a></li>
                <li><a href="withdrawal.php"><i class='bx bx-wallet'></i>Withdraw</a></li>
                <li><a href="deposit.php"><i class='bx bx-money'></i>Deposit</a></li>
                <li><a href="balance.php"><i class='bx bx-bar-chart'></i>Balance</a></li>
                <li><a href="send.php"><i class='bx bx-transfer'></i>Send Money</a></li>
                <li><a href="logout.php"><i class='bx bx-log-out'></i>Logout</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <h2>Personal Information</h2>
        <p>Manage your personal information.</p>

        <div class="info-grid">
            <div class="info-box">
                <label>Name</label>
                <div><?= htmlspecialchars($user['fname'] . ' ' . $user['mname'] . ' ' . $user['lname']) ?></div>
                <i class='bx bx-user'></i>
            </div>
            <div class="info-box">
                <label>Date of Birth</label>
                <div><?= htmlspecialchars($user['date_of_birth']) ?></div>
                <i class='bx bx-calendar'></i>
            </div>
            <div class="info-box">
                <label>Country Region</label>
                <div><?= htmlspecialchars($user['nationality']) ?></div>
                <i class='bx bx-world'></i>
            </div>
            <div class="info-box">
                <label>Currency</label>
                <div><?= htmlspecialchars($user['currency_type']) ?></div>
                <i class='bx bx-globe'></i>
            </div>
            <div class="info-box full">
                <label>Account Number</label>
                <div><?= htmlspecialchars($user['user_id']) ?></div>
                <i class='bx bx-envelope'></i>
            </div>
        </div>

        <h2>Change Password</h2>
        <p>Update your account password below.</p>

        <?php if (isset($password_message)) echo $password_message; ?>

        <form method="POST" action="">
            <label>Current Password:</label><br>
            <input type="password" name="current_password" required><br>

            <label>New Password:</label><br>
            <input type="password" name="new_password" required><br>

            <label>Confirm New Password:</label><br>
            <input type="password" name="confirm_new_password" required><br><br>

            <input type="submit" name="change_password" value="Change Password">
        </form>
    </main>
</div>
</body>
</html>