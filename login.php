<?php
include 'db.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    // If user is already logged in, redirect to dashboard
    echo "<script>window.location.href = 'dashboard.php';</script>";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier']; // Can be email or phone number
    $password = $_POST['password'];

    if (empty($identifier) || empty($password)) {
        $error = "Email/Phone and Password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ? OR phone_number = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                // Use JavaScript for redirection
                echo "<script>window.location.href = 'dashboard.php';</script>";
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "No account found with that email or phone number.";
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
    <title>Login - JazzCash Clone</title>
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
            max-width: 420px;
            text-align: center;
        }
        .logo {
            font-size: 28px;
            font-weight: 700;
            color: #e41e26;
            margin-bottom: 10px;
        }
        h2 {
            margin-bottom: 25px;
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
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .signup-link {
            margin-top: 20px;
            font-size: 14px;
        }
        .signup-link a {
            color: #e41e26;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="logo">JazzCash</div>
        <h2>Welcome Back!</h2>

        <?php if (!empty($error)): ?>
            <div class="message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <div class="form-group">
                <label for="identifier">Email or Phone Number</label>
                <input type="text" name="identifier" id="identifier" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </div>
            <button type="submit" class="button">Log In</button>
        </form>
        <div class="signup-link">
            Don't have an account? <a href="signup.php">Sign Up</a>
        </div>
    </div>

</body>
</html> 
