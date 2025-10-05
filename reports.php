<?php
session_start();
require 'db.php';
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
}
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT MONTH(transaction_date) as month, YEAR(transaction_date) as year, type, SUM(amount) as total FROM transactions WHERE user_id = ? GROUP BY month, year, type");
$stmt->execute([$user_id]);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - QuickBooks Clone</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.11.5/gsap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .chart-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            backdrop-filter: blur(5px);
        }
        canvas {
            max-width: 100%;
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
            .chart-container { padding: 15px; }
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
        <h2>Financial Reports</h2>
        <div class="chart-container">
            <canvas id="financialChart"></canvas>
        </div>
        <h3>Monthly Summary</h3>
        <table>
            <tr>
                <th>Month</th>
                <th>Year</th>
                <th>Type</th>
                <th>Total</th>
            </tr>
            <?php foreach ($reports as $report): ?>
                <tr>
                    <td><?php echo $report['month']; ?></td>
                    <td><?php echo $report['year']; ?></td>
                    <td><?php echo ucfirst($report['type']); ?></td>
                    <td>$<?php echo number_format($report['total'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <script>
        gsap.from(".chart-container", { duration: 1, y: 50, opacity: 0, ease: "power2.out" });
        gsap.from("table tr", { duration: 1, x: 50, opacity: 0, stagger: 0.1, delay: 0.5 });
        const ctx = document.getElementById('financialChart').getContext('2d');
        const data = {
            labels: [],
            datasets: [
                {
                    label: 'Income',
                    data: [],
                    backgroundColor: 'rgba(0, 196, 180, 0.5)',
                    borderColor: '#00c4b4',
                    borderWidth: 1
                },
                {
                    label: 'Expenses',
                    data: [],
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: '#ff6384',
                    borderWidth: 1
                }
            ]
        };
        <?php
        $months = [];
        $incomes = [];
        $expenses = [];
        foreach ($reports as $report) {
            $label = $report['month'] . '/' . $report['year'];
            if (!in_array($label, $months)) {
                $months[] = $label;
            }
            if ($report['type'] == 'income') {
                $incomes[$label] = $report['total'];
            } else {
                $expenses[$label] = $report['total'];
            }
        }
        echo "data.labels = " . json_encode($months) . ";";
        $income_data = [];
        $expense_data = [];
        foreach ($months as $month) {
            $income_data[] = isset($incomes[$month]) ? $incomes[$month] : 0;
            $expense_data[] = isset($expenses[$month]) ? $expenses[$month] : 0;
        }
        echo "data.datasets[0].data = " . json_encode($income_data) . ";";
        echo "data.datasets[1].data = " . json_encode($expense_data) . ";";
        ?>
        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>
