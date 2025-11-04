<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, balance FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $balance);
$stmt->fetch();
$stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - JazzCash Clone</title>
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header .logo {
            font-size: 24px;
            font-weight: 700;
        }
        .header .user-menu a {
            color: white;
            text-decoration: none;
            font-weight: 500;
            margin-left: 20px;
            transition: opacity 0.3s;
        }
        .header .user-menu a:hover {
            opacity: 0.8;
        }
        .main-container {
            padding: 30px;
        }
        .balance-card {
            background: linear-gradient(135deg, #e41e26, #a71219);
            color: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 20px rgba(228, 30, 38, 0.25);
        }
        .balance-card h2 {
            margin: 0 0 10px 0;
            font-weight: 300;
            font-size: 20px;
        }
        .balance-card .amount {
            margin: 0;
            font-weight: 700;
            font-size: 48px;
        }
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            text-align: center;
        }
        .service-item {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.07);
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .service-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        .service-item .icon {
            font-size: 42px;
            margin-bottom: 15px;
            color: #e41e26;
        }
        .service-item span {
            font-weight: 500;
            font-size: 16px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                padding: 20px;
            }
            .header .user-menu {
                margin-top: 15px;
            }
            .main-container {
                padding: 20px;
            }
            .balance-card .amount {
                font-size: 40px;
            }
            .services-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo">JazzCash</div>
        <div class="user-menu">
            <span>Welcome, <?php echo htmlspecialchars($full_name); ?></span>
            <a href="account.php"><i class="fas fa-user-circle"></i> Account</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="main-container">
        <div class="balance-card">
            <h2>Your Balance</h2>
            <p class="amount">PKR <?php echo number_format($balance, 2); ?></p>
        </div>

        <div class="services-grid">
            <a href="send_money.php" class="service-item">
                <div class="icon"><i class="fas fa-paper-plane"></i></div>
                <span>Send Money</span>
            </a>
            <a href="pay_bills.php" class="service-item">
                <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <span>Pay Bills</span>
            </a>
            <a href="recharge.php" class="service-item">
                <div class="icon"><i class="fas fa-mobile-alt"></i></div>
                <span>Mobile Recharge</span>
            </a>
            <a href="transactions.php" class="service-item">
                <div class="icon"><i class="fas fa-history"></i></div>
                <span>History</span>
            </a>
        </div>
    </div>

</body>
</html> 
