<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
}
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT SUM(amount) as total, type FROM transactions WHERE user_id = ? GROUP BY type");
$stmt->execute([$user_id]);
$summary = $stmt->fetchAll(PDO::FETCH_ASSOC);
$income = 0;
$expense = 0;
foreach ($summary as $row) {
    if ($row['type'] == 'income') $income = $row['total'];
    if ($row['type'] == 'expense') $expense = $row['total'];
}
$balance = $income - $expense;

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QuickBooks Clone</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
    <style>
        :root {
            --primary: #00c4b4;
            --secondary: #1e3c72;
        }
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background: #f4f7fa;
            color: #333;
        }
        .navbar {
            background: var(--secondary);
            padding: 15px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            transition: color 0.3s;
        }
        .navbar a:hover {
            color: var(--primary);
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        .summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
            clip-path: polygon(0 0, 100% 0, 100% 90%, 0 100%);
            transition: clip-path 0.5s;
        }
        .card:hover {
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }
        .card h3 {
            margin: 0 0 10px;
        }
        .transactions table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .transactions th, .transactions td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .transactions th {
            background: var(--secondary);
            color: #fff;
        }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; }
            .navbar a { margin: 5px 0; }
            .summary { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div>QuickBooks Clone</div>
        <div>
            <a href="dashboard.php" aria-label="Dashboard">Dashboard</a>
            <a href="invoices.php" aria-label="Invoices">Invoices</a>
            <a href="expenses.php" aria-label="Expenses">Expenses</a>
            <a href="reports.php" aria-label="Reports">Reports</a>
            <a href="logout.php" onclick="return confirm('Logout?')" aria-label="Logout">Logout</a>
        </div>
    </div>
    <div class="container">
        <h2>Financial Dashboard</h2>
        <div class="summary">
            <div class="card">
                <h3>Income</h3>
                <p>$<?php echo number_format($income, 2); ?></p>
            </div>
            <div class="card">
                <h3>Expenses</h3>
                <p>$<?php echo number_format($expense, 2); ?></p>
            </div>
            <div class="card">
                <h3>Balance</h3>
                <p>$<?php echo number_format($balance, 2); ?></p>
            </div>
        </div>
        <div class="transactions">
            <h3>Recent Transactions</h3>
            <table>
                <tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Description</th>
                </tr>
                <?php foreach ($transactions as $t): ?>
                    <tr>
                        <td><?php echo $t['transaction_date']; ?></td>
                        <td><?php echo ucfirst($t['type']); ?></td>
                        <td><?php echo $t['category']; ?></td>
                        <td>$<?php echo number_format($t['amount'], 2); ?></td>
                        <td><?php echo $t['description']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
    <script>
        gsap.from(".card", { duration: 1, x: -100, opacity: 0, stagger: 0.2, ease: "power2.out" });
        gsap.from(".navbar a", { duration: 0.8, y: -20, opacity: 0, stagger: 0.1, delay: 0.5 });
    </script>
</body>
</html>
