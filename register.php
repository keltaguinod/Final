<?php
// Connect to database
$host = "localhost";
$username = "root";
$password = "";
$database = "bank";

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";
// Form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $lname = $_POST['lname'];
    $dob = $_POST['date_of_birth'];
    $nationality = $_POST['nationality'];
    $balance = $_POST['balance'];
    $currency_type = $_POST['currency_type'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $message = "<p class='error'>Passwords do not match.</p>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO user (fname, mname, lname, date_of_birth, nationality, balance, currency_type, password)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $fname, $mname, $lname, $dob, $nationality, $balance, $currency_type, $hashed_password);

        if ($stmt->execute()) {
            $message = "<p class='success'>User registered successfully! You may now login.</p>";
        } else {
            $message = "<p class='error'>Error: " . $stmt->error . "</p>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Account</title>
    <link rel="stylesheet" href="register.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <nav>
            <ul>
                <li class="active"><a href="signin.php">Sign in</a></li>
                <li><a href="dashboard.php">back</a></li>
            </ul>
        </nav>
    </aside>

    <main class="main-content">
        <div class="form-title">
            <h2>Create a New Account</h2>
            <p>Fill out the form to create a new user account.</p>
        </div>

        <?php echo $message; ?>

        <div class="form-wrapper">
            <form method="POST" action="">
                <label>First Name:</label>
                <input type="text" name="fname" required>

                <label>Middle Name:</label>
                <input type="text" name="mname">

                <label>Last Name:</label>
                <input type="text" name="lname" required>

                <label>Date of Birth:</label>
                <input type="date" name="date_of_birth" required>

                <label>Nationality:</label>
                <input type="text" name="nationality">

                <label>Balance:</label>
                <input type="number" step="0.01" name="balance" value="0.00">

                <label>Currency Type:</label>
                <select name="currency_type" required>
                    <option value="PHP">PHP</option>
                    <option value="USD">USD</option>
                    <option value="YEN">YEN</option>
                </select>

                <label>Password:</label>
                <input type="password" name="password" required>

                <label>Confirm Password:</label>
                <input type="password" name="confirm_password" required>

                <input type="submit" value="Register">
            </form>
        </div>
    </main>
</div>
</body>
</html>
