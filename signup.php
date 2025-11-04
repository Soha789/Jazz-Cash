<?php
include 'db.php';

// SQL to create users table if it doesn't exist
$sql_users = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone_number VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    wallet_pin VARCHAR(255) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 1000.00, -- Default 1000 for testing
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

// SQL to create transactions table if it doesn't exist
$sql_transactions = "
CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);";

// Execute table creation queries
if (!$conn->query($sql_users) || !$conn->query($sql_transactions)) {
    die("Error creating table: " . $conn->error);
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $phone_number = $_POST['phone_number'];
    $password = $_POST['password'];
    $wallet_pin = $_POST['wallet_pin'];

    if (empty($full_name) || empty($email) || empty($phone_number) || empty($password) || empty($wallet_pin)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/^[0-9]{4}$/', $wallet_pin)) {
        $error = "PIN must be exactly 4 digits.";
    } else {
        // Check if email or phone number already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR phone_number = ?");
        $stmt->bind_param("ss", $email, $phone_number);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "An account with this email or phone number already exists.";
        } else {
            // Hash password and PIN
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $hashed_pin = password_hash($wallet_pin, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, phone_number, password, wallet_pin) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $phone_number, $hashed_password, $hashed_pin);

            if ($stmt->execute()) {
                $success = "Registration successful! You will be redirected to the login page.";
            } else {
                $error = "Error: " . $stmt->error;
            }
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
    <title>Sign Up - JazzCash Clone</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #333;
        }
        .container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #e41e26;
            margin-bottom: 10px;
        }
        h2 {
            margin-bottom: 20px;
            color: #555;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #e41e26;
        }
        .button {
            background-color: #e41e26;
            color: white;
            padding: 14px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #c3131a;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .login-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #e41e26;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">JazzCash</div>
        <h2>Create Your Account</h2>

        <?php if (!empty($error)): ?>
            <div class="message error"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="message success"><?php echo $success; ?></div>
        <?php endif; ?>

        <form action="signup.php" method="post">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" name="full_name" id="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number</label>
                <input type="text" name="phone_number" id="phone_number" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
             <div class="form-group">
                <label for="wallet_pin">4-Digit Wallet PIN</label>
                <input type="password" name="wallet_pin" id="wallet_pin" required maxlength="4" pattern="\d{4}">
            </div>
            <button type="submit" class="button">Sign Up</button>
        </form>
        <div class="login-link">
            Already have an account? <a href="login.php">Log In</a>
        </div>
    </div>

    <script>
        <?php if (!empty($success)): ?>
        setTimeout(function() {
            window.location.href = 'login.php';
        }, 3000); // Redirect after 3 seconds
        <?php endif; ?>
    </script>

</body>
</html> 
