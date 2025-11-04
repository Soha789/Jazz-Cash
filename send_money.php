<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch sender's current balance and wallet PIN for verification
$stmt = $conn->prepare("SELECT balance, wallet_pin FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($sender_balance, $hashed_pin);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipient_phone = $_POST['recipient_phone'];
    $amount = $_POST['amount'];
    $wallet_pin = $_POST['wallet_pin'];
    $description = $_POST['description'];

    // Basic validation
    if (empty($recipient_phone) || empty($amount) || empty($wallet_pin)) {
        $error = "All fields except description are required.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Invalid amount.";
    } elseif (!password_verify($wallet_pin, $hashed_pin)) {
        $error = "Incorrect wallet PIN.";
    } elseif ($amount > $sender_balance) {
        $error = "Insufficient balance.";
    } else {
        // Check if recipient exists and is not the sender
        $stmt = $conn->prepare("SELECT id FROM users WHERE phone_number = ? AND id != ?");
        $stmt->bind_param("si", $recipient_phone, $user_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($recipient_id);
            $stmt->fetch();

            // Start transaction
            $conn->begin_transaction();

            try {
                // Deduct from sender
                $new_sender_balance = $sender_balance - $amount;
                $update_sender = $conn->prepare("UPDATE users SET balance = ? WHERE id = ?");
                $update_sender->bind_param("di", $new_sender_balance, $user_id);
                $update_sender->execute();

                // Add to recipient
                $update_recipient = $conn->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
                $update_recipient->bind_param("di", $amount, $recipient_id);
                $update_recipient->execute();

                // Record transaction for sender
                $desc_sender = "Sent to " . $recipient_phone . ($description ? ": " . $description : "");
                $record_sender = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'Debit', ?, ?)");
                $record_sender->bind_param("ids", $user_id, $amount, $desc_sender);
                $record_sender->execute();

                // Record transaction for recipient
                $desc_recipient = "Received from your contact" . ($description ? ": " . $description : "");
                $record_recipient = $conn->prepare("INSERT INTO transactions (user_id, type, amount, description) VALUES (?, 'Credit', ?, ?)");
                $record_recipient->bind_param("ids", $recipient_id, $amount, $desc_recipient);
                $record_recipient->execute();

                $conn->commit();
                $success = "Money sent successfully!";
                // Update balance for the view
                $sender_balance = $new_sender_balance;

            } catch (Exception $e) {
                $conn->rollback();
                $error = "Transaction failed. Please try again.";
            }

        } else {
            $error = "Recipient not found or you're trying to send to yourself.";
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Money - JazzCash Clone</title>
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
            max-width: 500px;
            margin: 0 auto;
        }
        .form-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.07);
        }
        .form-card h2 {
            text-align: center;
            margin-top: 0;
            margin-bottom: 25px;
            font-weight: 600;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        .button {
            background-color: #e41e26;
            color: white;
            padding: 14px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
            text-align: center;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo">JazzCash</div>
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </header>

    <div class="container">
        <div class="form-card">
            <h2>Send Money</h2>
            <p style="text-align:center; font-weight: 500; margin-bottom: 20px;">Your Balance: PKR <?php echo number_format($sender_balance, 2); ?></p>
            
            <?php if ($error) echo "<div class='message error'>$error</div>"; ?>
            <?php if ($success) echo "<div class='message success'>$success</div>"; ?>

            <form action="send_money.php" method="post">
                <div class="form-group">
                    <label for="recipient_phone">Recipient's Phone Number</label>
                    <input type="text" name="recipient_phone" id="recipient_phone" required>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (PKR)</label>
                    <input type="number" step="0.01" name="amount" id="amount" required>
                </div>
                <div class="form-group">
                    <label for="description">Description (Optional)</label>
                    <input type="text" name="description" id="description">
                </div>
                <div class="form-group">
                    <label for="wallet_pin">Your 4-Digit PIN</label>
                    <input type="password" name="wallet_pin" id="wallet_pin" required maxlength="4">
                </div>
                <button type="submit" class="button">Send</button>
            </form>
        </div>
    </div>

</body>
</html> 
