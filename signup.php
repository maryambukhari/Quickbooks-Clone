<?php
session_start();
require 'db.php';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $csrf_token = $_POST['csrf_token'];

    if ($csrf_token !== $_SESSION['csrf_token']) {
        echo "<script>alert('CSRF validation failed.');</script>";
    } else {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            echo "<script>alert('Email already exists.');</script>";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$username, $email, $password])) {
                echo "<script>alert('Signup successful! Please login.'); window.location.href='login.php';</script>";
            } else {
                echo "<script>alert('Error during signup.');</script>";
            }
        }
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup - QuickBooks Clone</title>
    <style>
        :root {
            --primary: #00c4b4;
            --secondary: #667eea;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, var(--secondary), #764ba2);
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .form-container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2em;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        input:focus {
            outline: 2px solid var(--primary);
        }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 12px;
            cursor: pointer;
            border-radius: 8px;
            width: 100%;
            font-size: 1.1em;
            transition: transform 0.3s, background 0.3s;
            clip-path: polygon(10% 0, 100% 0, 90% 100%, 0 100%);
        }
        .btn:hover {
            transform: scale(1.05);
            background: #009688;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }
        @media (max-width: 768px) {
            .form-container {
                padding: 20px;
            }
            h2 {
                font-size: 1.5em;
            }
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Sign Up</h2>
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="text" name="username" placeholder="Username" required aria-label="Username">
            <input type="email" name="email" placeholder="Email" required aria-label="Email">
            <input type="password" name="password" placeholder="Password" required aria-label="Password">
            <button type="submit" class="btn" aria-label="Sign Up">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php" style="color: var(--primary);">Login</a></p>
    </div>
</body>
</html>
