<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
}
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $description = $_POST['description'];
    $transaction_date = $_POST['transaction_date'];
    $csrf_token = $_POST['csrf_token'];

    if ($csrf_token !== $_SESSION['csrf_token']) {
        echo "<script>alert('CSRF validation failed.');</script>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO transactions (user_id, type, category, amount, description, transaction_date) VALUES (?, ?, ?, ?, ?, ?)");
        if ($stmt->execute([$user_id, $type, $category, $amount, $description, $transaction_date])) {
            echo "<script>alert('Transaction added!'); window.location.href='expenses.php';</script>";
        }
    }
}

$stmt = $pdo->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY transaction_date DESC");
$stmt->execute([$user_id]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expenses - QuickBooks Clone</title>
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
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            backdrop-filter: blur(5px);
        }
        input, select {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 8px;
            width: 100%;
        }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 8px;
            transition: transform 0.3s;
        }
        .btn:hover {
            transform: scale(1.05);
            background: #009688;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: var(--secondary);
            color: #fff;
        }
        @media (max-width: 768px) {
            .form-container { padding: 15px; }
            table { font-size: 0.9em; }
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
        <h2>Add Transaction</h2>
        <div class="form-container">
            <form id="transaction-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <select name="type" required aria-label="Transaction Type">
                    <option value="income">Income</option>
                    <option value="expense">Expense</option>
                </select>
                <input type="text" name="category" placeholder="Category (e.g., Salary, Rent)" required aria-label="Category">
                <input type="number" name="amount" placeholder="Amount" step="0.01" required aria-label="Amount">
                <input type="text" name="description" placeholder="Description" aria-label="Description">
                <input type="date" name="transaction_date" required aria-label="Transaction Date">
                <button type="submit" class="btn" aria-label="Add Transaction">Add Transaction</button>
            </form>
        </div>
        <h2>Your Transactions</h2>
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
    <script>
        gsap.from(".form-container", { duration: 1, y: 50, opacity: 0, ease: "power2.out" });
        gsap.from("table tr", { duration: 1, x: 50, opacity: 0, stagger: 0.1, delay: 0.5 });
        document.getElementById('transaction-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const response = await fetch('expenses.php', { method: 'POST', body: formData });
            const text = await response.text();
            document.body.innerHTML = text;
        });
    </script>
</body>
</html>
