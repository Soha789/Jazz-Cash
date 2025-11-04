<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user's balance and PIN
$stmt = $conn->prepare("SELECT balance, wallet_pin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($balance, $hashed_pin);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $operator = $_POST['operator'];
    $phone_number = $_POST['phone_number'];
    $amount = $_POST['amount'];
    $wallet_pin = $_POST['wallet_pin'];

    if (empty($operator) || empty($phone_number) || empty($amount) || empty($wallet_pin)) {
        $error = "All fields are required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Invalid amount.";
    } elseif (!password_verify($wallet_pin, $hashed_pin)) {
        $error = "Incorrect wallet PIN.";
    } elseif ($amount > $balance) {
        $error = "Insufficient balance.";
    } else {
        $conn->begin_transaction();
        try {
            // Deduct from balance
            $new_balance = $balance - $amount;
            $update_user = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
            $update_user->bind_param("di", $new_balance, $user_id);
            $update_user->execute();

            // Record transaction
            $description = "Mobile recharge for " . htmlspecialchars($operator) . " (" . htmlspecialchars($phone_number) . ")";
            $record_trans = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'Debit', ?, ?)");
            $record_trans->bind_param("ids", $user_id, $amount, $description);
            $record_trans->execute();
            
            $conn->commit();
            $success = "Mobile recharge successful!";
            $balance = $new_balance;

        } catch (Exception $e) {
            $conn->rollback();
            $error = "Transaction failed. Please try again.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile Recharge - JazzCash Clone</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .header { background-color: #e41e26; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header .logo { font-size: 24px; font-weight: 700; }
        .header a { color: white; text-decoration: none; font-weight: 500; }
        .container { padding: 30px; max-width: 500px; margin: 0 auto; }
        .form-card { background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.07); }
        .form-card h2 { text-align: center; margin-top: 0; margin-bottom: 25px; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input, select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        .button { background-color: #e41e26; color: white; padding: 14px 20px; border: none; border-radius: 8px; cursor: pointer; width: 100%; font-size: 16px; font-weight: 600; }
        .message { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: 500; text-align: center; }
        .error { background-color: #f8d7da; color: #721c24; }
        .success { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <header class="header">
        <div class="logo">JazzCash</div>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </header>
    <div class="container">
        <div class="form-card">
            <h2>Mobile Recharge</h2>
            <p style="text-align:center; font-weight: 500; margin-bottom: 20px;">Your Balance: PKR <?php echo number_format($balance, 2); ?></p>
            <?php if ($error) echo "<div class='message error'>$error</div>"; ?>
            <?php if ($success) echo "<div class='message success'>$success</div>"; ?>
            <form action="recharge.php" method="post">
                <div class="form-group">
                    <label for="operator">Operator</label>
                    <select name="operator" id="operator" required>
                        <option value="Jazz">Jazz</option>
                        <option value="Telenor">Telenor</option>
                        <option value="Ufone">Ufone</option>
                        <option value="Zong">Zong</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="phone_number">Phone Number</label>
                    <input type="text" name="phone_number" id="phone_number" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (PKR)</label>
                    <input type="number" step="1" name="amount" id="amount" required>
                </div>
                <div class="form-group">
                    <label for="wallet_pin">Your 4-Digit PIN</label>
                    <input type="password" name="wallet_pin" id="wallet_pin" required maxlength="4">
                </div>
                <button type="submit" class="button">Recharge Now</button>
            </form>
        </div>
    </div>
</body>
</html> 
