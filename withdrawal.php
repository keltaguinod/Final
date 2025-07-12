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

//user info
$sql = "SELECT fname, mname, lname FROM user WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$stmt->close();

//current balance
$balance_query = "SELECT SUM(amount) AS balance FROM transactions WHERE account_id = ?";
$stmt = $conn->prepare($balance_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$balance_result = $stmt->get_result();
$balance_row = $balance_result->fetch_assoc();
$current_balance = $balance_row['balance'] ?? 0.00;
$stmt->close();

$message = "";

//withdraw and transaction record
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount'])) {
    $withdraw_amount = floatval($_POST['amount']);

    if ($withdraw_amount <= 0) {
        $message = "<p style='color:red;'>Please enter a valid amount.</p>";
    } elseif ($withdraw_amount > $current_balance) {
        $message = "<p style='color:red;'>Insufficient balance.</p>";
    } else {
        $details = "Withdrawal";
        $transaction_query = "INSERT INTO transactions (account_id, amount, details, transaction_date) VALUES (?, ?, ?, CURDATE())";
        $stmt = $conn->prepare($transaction_query);
        $negative_amount = -$withdraw_amount;
        $stmt->bind_param("ids", $user_id, $negative_amount, $details);

        if ($stmt->execute()) {
            $current_balance -= $withdraw_amount;

            //update balance in user table
            $update_balance_query = "UPDATE user SET balance = ? WHERE user_id = ?";
            $update_stmt = $conn->prepare($update_balance_query);
            $update_stmt->bind_param("di", $current_balance, $user_id);
            $update_stmt->execute();
            $update_stmt->close();

            $message = "<p style='color:green;'>Withdrawal successful.</p>";
        } else {
            $message = "<p style='color:red;'>Transaction failed.</p>";
        }

        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Withdraw - Banking System</title>
    <link rel="stylesheet" href="withdrawal.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <h2>PiggyBank</h2>
        <ul>
            <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="personalinfo.php"><i class='bx bxs-user'></i>Personal Information</a></li>
            <li class="active"><a href="withdrawal.php"><i class='bx bx-wallet'></i>Withdraw</a></li>
            <li><a href="deposit.php"><i class='bx bx-money'></i>Deposit</a></li>
            <li><a href="balance.php"><i class='bx bx-bar-chart'></i>Balance</a></li>
            <li><a href="send.php"><i class='bx bx-transfer'></i>Send Money</a></li>
            <li><a href="logout.php"><i class='bx bx-log-out'></i>Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Withdraw</h2>
        <p>Enter the details below to process a withdrawal.</p>

        <?= $message ?>

        <div class="form-container">
            <div class="row">
                <div class="column">
                    <label>Account Number</label>
                    <div><?= htmlspecialchars($user_id) ?></div>
                </div>
                <div class="column">
                    <label>Name</label>
                    <div><?= htmlspecialchars($user_data['fname'] . ' ' . $user_data['mname'] . ' ' . $user_data['lname']) ?></div>
                </div>
            </div>
            <div class="row">
                <div class="column">
                    <label>Balance</label>
                    <div>₱<?= number_format($current_balance, 2) ?></div>
                </div>
                <div class="column">
                    <form method="post" action="">
                        <label>Withdraw Amount</label>
                        <input type="number" placeholder="₱0.00" min="0" name="amount" required>
                        <div class="buttons">
                            <button type="submit">Submit</button>
                            <button type="button" onclick="window.location='withdrawal.php'">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>