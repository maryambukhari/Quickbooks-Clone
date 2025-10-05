<?php
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickBooks Clone - Home</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
    <style>
        :root {
            --primary: #00c4b4;
            --secondary: #1e3c72;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: linear-gradient(135deg, var(--secondary), #2a5298);
            color: #fff;
            overflow-x: hidden;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px;
            text-align: center;
        }
        h1 {
            font-size: 3.5em;
            margin-bottom: 20px;
        }
        p {
            font-size: 1.3em;
            margin-bottom: 40px;
        }
        .btn {
            padding: 15px 30px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            cursor: pointer;
            transition: transform 0.3s, background 0.3s;
            clip-path: polygon(10% 0, 100% 0, 90% 100%, 0 100%);
        }
        .btn:hover {
            transform: scale(1.05);
            background: #009688;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-heading { animation: fadeInDown 1s ease-in; }
        @media (max-width: 768px) {
            h1 { font-size: 2.5em; }
            p { font-size: 1em; }
            .container { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="animate-heading">QuickBooks Clone</h1>
        <p>Manage your finances effortlessly with invoices, expense tracking, and insightful reports.</p>
        <button class="btn" onclick="window.location.href='signup.php'" aria-label="Get Started">Get Started</button>
        <button class="btn" onclick="window.location.href='login.php'" aria-label="Login">Login</button>
    </div>
    <script>
        gsap.from(".btn", { duration: 1, y: 50, opacity: 0, stagger: 0.2, ease: "power2.out" });
    </script>
</body>
</html>
