<?php
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href = 'login.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, phone_number, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($full_name, $email, $phone_number, $created_at);
$stmt->fetch();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $error = "New passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $error = "New password must be at least 6 characters long.";
        } else {
            // Verify current password
            $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->bind_result($hashed_password);
            $stmt->fetch();
            $stmt->close();

            if (password_verify($current_password, $hashed_password)) {
                // Hash new password and update
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $update_stmt->bind_param("si", $new_hashed_password, $user_id);
                if ($update_stmt->execute()) {
                    $success = "Password updated successfully.";
                } else {
                    $error = "Error updating password.";
                }
                $update_stmt->close();
            } else {
                $error = "Incorrect current password.";
            }
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
    <title>My Account - JazzCash Clone</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
        body { font-family: 'Poppins', sans-serif; background-color: #f4f7f6; margin: 0; color: #333; }
        .header { background-color: #e41e26; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .header .logo { font-size: 24px; font-weight: 700; }
        .header a { color: white; text-decoration: none; font-weight: 500; }
        .container { padding: 30px; max-width: 700px; margin: 0 auto; }
        .card { background-color: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.07); margin-bottom: 30px; }
        .card h2 { margin-top: 0; margin-bottom: 25px; font-weight: 600; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .info-item { background-color: #f9f9f9; padding: 15px; border-radius: 8px; }
        .info-item label { font-weight: 500; color: #555; display: block; margin-bottom: 5px; }
        .info-item span { font-size: 16px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
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
        <div class="card">
            <h2>Account Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <span><?php echo htmlspecialchars($full_name); ?></span>
                </div>
                <div class="info-item">
                    <label>Email Address</label>
                    <span><?php echo htmlspecialchars($email); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone Number</label>
                    <span><?php echo htmlspecialchars($phone_number); ?></span>
                </div>
                <div class="info-item">
                    <label>Member Since</label>
                    <span><?php echo date("d M, Y", strtotime($created_at)); ?></span>
                </div>
            </div>
        </div>
        <div class="card">
            <h2>Change Password</h2>
             <?php if ($error) echo "<div class='message error'>$error</div>"; ?>
            <?php if ($success) echo "<div class='message success'>$success</div>"; ?>
            <form action="account.php" method="post">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input type="password" name="current_password" id="current_password" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" name="new_password" id="new_password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                </div>
                <button type="submit" name="change_password" class="button">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html> 
