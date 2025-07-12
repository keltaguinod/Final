<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

//conect to database
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

//send money
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['amount']) && isset($_POST['recipient'])) {
    $amount = floatval($_POST['amount']);
    $recipient_id = intval($_POST['recipient']);

    if ($amount <= 0) {
        $message = "<p style='color:red;'>Invalid amount.</p>";
    } elseif ($recipient_id === $user_id) {
        $message = "<p style='color:red;'>You cannot send money to yourself.</p>";
    } elseif ($amount > $current_balance) {
        $message = "<p style='color:red;'>Insufficient balance.</p>";
    } else {
        //check if recipient exists
        $stmt = $conn->prepare("SELECT user_id FROM user WHERE user_id = ?");
        $stmt->bind_param("i", $recipient_id);
        $stmt->execute();
        $recipient_result = $stmt->get_result();

        if ($recipient_result->num_rows === 0) {
            $message = "<p style='color:red;'>Recipient account not found.</p>";
        } else {
            $stmt->close();
            $conn->begin_transaction();
            try {
                //sender transaction
                $sender_details = "Sent to $recipient_id";
                $stmt = $conn->prepare("INSERT INTO transactions (account_id, amount, details, transaction_date) VALUES (?, ?, ?, CURDATE())");
                $negative_amount = -$amount;
                $stmt->bind_param("ids", $user_id, $negative_amount, $sender_details);
                $stmt->execute();
                $stmt->close();

                //recipient transaction
                $recipient_details = "Received from $user_id";
                $stmt = $conn->prepare("INSERT INTO transactions (account_id, amount, details, transaction_date) VALUES (?, ?, ?, CURDATE())");
                $stmt->bind_param("ids", $recipient_id, $amount, $recipient_details);
                $stmt->execute();
                $stmt->close();

                //update sender balance
                $current_balance -= $amount;
                $stmt = $conn->prepare("UPDATE user SET balance = ? WHERE user_id = ?");
                $stmt->bind_param("di", $current_balance, $user_id);
                $stmt->execute();
                $stmt->close();

                //update recipient balance
                $stmt = $conn->prepare("SELECT balance FROM user WHERE user_id = ?");
                $stmt->bind_param("i", $recipient_id);
                $stmt->execute();
                $recipient_balance_result = $stmt->get_result();
                $recipient_balance_row = $recipient_balance_result->fetch_assoc();
                $recipient_balance = $recipient_balance_row['balance'] ?? 0.00;
                $stmt->close();

                $recipient_balance += $amount;
                $stmt = $conn->prepare("UPDATE user SET balance = ? WHERE user_id = ?");
                $stmt->bind_param("di", $recipient_balance, $recipient_id);
                $stmt->execute();
                $stmt->close();

                $conn->commit();
                $message = "<p style='color:green;'>Money sent successfully.</p>";
            } catch (Exception $e) {
                $conn->rollback();
                $message = "<p style='color:red;'>Transaction failed: " . $e->getMessage() . "</p>";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Money</title>
    <link rel="stylesheet" href="deposit.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>
<body>
<div class="container">
    <aside class="sidebar">
        <h2>PiggyBank</h2>
        <ul>
            <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="personalinfo.php"><i class='bx bxs-user'></i>Personal Information</a></li>
            <li><a href="withdrawal.php"><i class='bx bx-wallet'></i>Withdraw</a></li>
            <li><a href="deposit.php"><i class='bx bx-money'></i>Deposit</a></li>
            <li><a href="balance.php"><i class='bx bx-bar-chart'></i>Balance</a></li>
            <li class="active"><a href="send.php"><i class='bx bx-send'></i>Send Money</a></li>
            <li><a href="logout.php"><i class='bx bx-log-out'></i>Logout</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <h2>Send Money</h2>
        <p>Fill out the form below to send money to another account.</p>
        <p>Current Overview:</p>
        <div class="balance-box">
            <h2>₱<?= number_format($current_balance, 2) ?></h2>
        </div>

        <?= $message ?>

        <div class="form-container">
            <div class="row">
                <div class="column">
                    <div>
                        <strong>Sender Account #: </strong> <?= htmlspecialchars($user_id) ?><br>
                        <strong>Name:</strong> <?= htmlspecialchars($user_data['fname'] . ' ' . $user_data['mname'] . ' ' . $user_data['lname']) ?>
                    </div>
                </div>
            </div>
        </div>

        <form method="post" action="">
            <div class="row">
                <div class="column">
                    <label>Recipient Account Number</label>
                    <input type="number" name="recipient" required>
                </div>
                <div class="column">
                    <label>Amount to Send</label>
                    <input type="number" name="amount" min="1" required>
                </div>
            </div>
            <div class="buttons">
                <button type="submit">Submit</button>
                <button type="button" onclick="window.location='send.php'">Cancel</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>