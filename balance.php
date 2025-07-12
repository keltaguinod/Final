<?php
session_start();

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

//current balance
$balance_query = "SELECT SUM(amount) AS balance FROM transactions WHERE account_id = ?";
$stmt = $conn->prepare($balance_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$balance_result = $stmt->get_result();
$balance_row = $balance_result->fetch_assoc();
$current_balance = $balance_row['balance'] ?? 0.00;
$stmt->close();

//transactions
$transactions_query = "SELECT amount, details, transaction_date FROM transactions WHERE account_id = ? ORDER BY transaction_date DESC";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_result = $stmt->get_result();
$transactions = [];
while ($row = $transactions_result->fetch_assoc()) {
    $transactions[] = $row;
}
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bank Balance</title>
    <link rel="stylesheet" href="balance.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
    <div class="sidebar">
        <h2>PiggyBank</h2>
        <ul>
            <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="personalinfo.php"><i class='bx bxs-user'></i>Personal Information</a></li>
            <li><a href="withdrawal.php"><i class='bx bx-wallet'></i>Withdraw</a></li>
            <li><a href="deposit.php"><i class='bx bx-money'></i>Deposit</a></li>
            <li class="active"><a href="balance.php"><i class='bx bx-bar-chart'></i>Balance</a></li>
            <li><a href="send.php"><i class='bx bx-transfer'></i>Send Money</a></li>
            <li><a href="logout.php"><i class='bx bx-log-out'></i>Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>Balance Account</h1>
            <p>Balance Overview</p>
        </div>

        <div class="balance-box">
            <h2>₱ <?= number_format($current_balance, 2) ?></h2>
        </div>

        <div class="transactions">
            <h3>Transaction History</h3>
            <ul>
                <?php if (empty($transactions)): ?>
                    <li>No transactions found.</li>
                <?php else: ?>
                    <?php foreach ($transactions as $txn): ?>
                        <li class="<?= $txn['amount'] >= 0 ? 'positive' : 'negative' ?>">
                            <?= ($txn['amount'] >= 0 ? '+ ' : '- ') ?>₱<?= number_format(abs($txn['amount']), 2) ?>
                            - <?= htmlspecialchars($txn['details']) ?>
                            <small>(<?= htmlspecialchars($txn['transaction_date']) ?>)</small>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>