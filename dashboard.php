<?php
session_start();

// Redirect if not logged in
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

$balance_query = "SELECT SUM(amount) AS balance FROM transactions WHERE account_id = ?";
$stmt = $conn->prepare($balance_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$balance_result = $stmt->get_result();
$balance_row = $balance_result->fetch_assoc();
$current_balance = $balance_row['balance'] ?? 0.00;
$stmt->close();

$transactions_query = "SELECT amount, details, transaction_date FROM transactions WHERE account_id = ? ORDER BY transaction_date DESC LIMIT 5";
$stmt = $conn->prepare($transactions_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$transactions_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="dashboard.css">
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <title>PiggyBank</title>
</head>
<body>

<section id="sidebar">
    <a href="#" class="brand">
        <img src="piggylogo.png" alt="PiggyBank Logo" class="logo-img">
        <span class="text">PiggyBank</span>
    </a>

    <ul class="side-menu top">
        <li class="active"><a href="dashboard.php"><i class='bx bxs-dashboard'></i><span class="text">Dashboard</span></a></li>
        <li><a href="personalinfo.php"><i class='bx bxs-shopping-bag-alt'></i><span class="text">Personal Information</span></a></li>
        <li><a href="withdrawal.php"><i class='bx bxs-doughnut-chart'></i><span class="text">Withdraw</span></a></li>
        <li><a href="deposit.php"><i class='bx bxs-message-dots'></i><span class="text">Deposit</span></a></li>
        <li><a href="balance.php"><i class='bx bxs-group'></i><span class="text">Balance</span></a></li>
        <li><a href="send.php"><i class='bx bx-transfer'></i><span class="text">Send Money</span></a></li>
    </ul>

    <ul class="side-menu">
        <li><a href="logout.php" class="logout"><i class='bx bxs-log-out-circle'></i><span class="text">Logout</span></a></li>
    </ul>
</section>

<!-- CONTENT -->
<section id="content">
    <!-- NAVBAR -->
	<nav>
		<i class='bx bx-menu'></i>
		<a href="#" class="nav-link">Categories</a>
		<form action="#">
			<div class="form-input">
				<input type="search" placeholder="Search...">
				<button type="submit" class="search-btn"><i class='bx bx-search'></i></button>
			</div>
		</form>

		<div class="nav-user" style="padding: 8px 16px; color: white;">
			Welcome, <?= htmlspecialchars($_SESSION['user']['fname']) ?>!
		</div>
	</nav>


    <!-- MAIN -->
    <main>
        <div class="head-title">
            <div class="left">
                <h1>Dashboard</h1>
                <ul class="breadcrumb">
                    <li><a href="#">Dashboard</a></li>
                    <li><i class='bx bx-chevron-right'></i></li>
                    <li><a class="active" href="#">Home</a></li>
                </ul>
            </div>
        </div>

        <ul class="box-info">
            <li>
                <i class='bx bxs-calendar-check'></i>
                <span class="text">
                    <h2>
                        <?php
                        $today = date("F j, Y");
                        echo "<span style='color: white;'>$today</span>";
                        ?>
                    </h2>
                    <p>calendar</p>
                </span>
            </li>

            <li>
                <i class='bx bxs-cloud'></i>
                <span class="text">
                    <?php include 'weather.php'; ?>
                </span>
            </li>

            <li>
                <i class='bx bxs-dollar-circle'></i>
                <span class="text">
                    <h2>
                        <p><?= number_format($current_balance, 2) ?></p>
                    </h2>
                    <p>Current Balance</p>
                </span>
            </li>
        </ul>

        <div class="table-data">
            <div class="order">
                <div class="head">
                    <h3>Recent Transactions</h3>
                    <i class='bx bx-search'></i>
                    <i class='bx bx-filter'></i>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Amount</th>
                            <th>Details</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $transactions_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= number_format($row['amount'], 2) ?></td>
                                <td><?= htmlspecialchars($row['details']) ?></td>
                                <td><?= htmlspecialchars($row['transaction_date']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <div class="todo">
                <div class="head">
                    <h3>Notifications</h3>
                    <i class='bx bx-plus'></i>
                    <i class='bx bx-filter'></i>
                </div>
                <ul class="noti-list">
                   <i class='bx bx-dots-vertical-rounded'></i></li> <li class="completed"><p>You have successfully deposited PHP 50,000.00 to your account.</p>
                   <i class='bx bx-dots-vertical-rounded'></i></li> <li class="completed"><p>A withdrawal of PHP 2,500.00 has been made from your account.</p>
                   <i class='bx bx-dots-vertical-rounded'></i></li> <li class="completed"><p>You sent PHP 15,000.00 to Account 2
                </ul>
            </div>
        </div>
    </main>
</section>  

</body>
</html>