<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch transactions for the user, ordered by the most recent
$stmt = $conn->prepare("SELECT type, amount, description, created_at FROM transactions WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$transactions = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - JazzCash Clone</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            color: #333;
        }
        .header {
            background-color: #e41e26;
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header .logo {
            font-size: 24px;
            font-weight: 700;
        }
        .header a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        .container {
            padding: 30px;
            max-width: 800px;
            margin: 0 auto;
        }
        .history-card {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.07);
        }
        .history-card h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .transaction-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .transaction-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .transaction-item:last-child {
            border-bottom: none;
        }
        .transaction-details {
            display: flex;
            align-items: center;
        }
        .transaction-icon {
            font-size: 24px;
            margin-right: 20px;
            width: 40px;
            height: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
        }
        .transaction-icon.credit {
            color: #28a745;
            background-color: #e9f7ec;
        }
        .transaction-icon.debit {
            color: #dc3545;
            background-color: #fdeeee;
        }
        .transaction-description {
            font-weight: 500;
        }
        .transaction-date {
            font-size: 13px;
            color: #777;
        }
        .transaction-amount {
            font-weight: 600;
            font-size: 16px;
        }
        .transaction-amount.credit {
            color: #28a745;
        }
        .transaction-amount.debit {
            color: #dc3545;
        }
        .no-transactions {
            text-align: center;
            padding: 40px;
            color: #777;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo">JazzCash</div>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </header>

    <div class="container">
        <div class="history-card">
            <h2>Transaction History</h2>
            <?php if (count($transactions) > 0): ?>
                <ul class="transaction-list">
                    <?php foreach ($transactions as $t): ?>
                        <li class="transaction-item">
                            <div class="transaction-details">
                                <div class="transaction-icon <?php echo strtolower($t['type']); ?>">
                                    <i class="fas fa-<?php echo ($t['type'] == 'Credit') ? 'arrow-down' : 'arrow-up'; ?>"></i>
                                </div>
                                <div>
                                    <div class="transaction-description"><?php echo htmlspecialchars($t['description']); ?></div>
                                    <div class="transaction-date"><?php echo date("d M, Y h:i A", strtotime($t['created_at'])); ?></div>
                                </div>
                            </div>
                            <div class="transaction-amount <?php echo strtolower($t['type']); ?>">
                                <?php echo ($t['type'] == 'Credit' ? '+' : '-') . ' PKR ' . number_format($t['amount'], 2); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="no-transactions">
                    <p>You have no transactions yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</body>
</html> 
