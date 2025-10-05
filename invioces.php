<?php
session_start();
require 'db.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];

// Handle client creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_client'])) {
    $client_name = trim($_POST['client_name']);
    $client_email = trim($_POST['client_email']);
    $csrf_token = $_POST['csrf_token'];

    if ($csrf_token !== $_SESSION['csrf_token']) {
        echo "<script>alert('CSRF validation failed.');</script>";
    } elseif (empty($client_name) || empty($client_email) || !filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid client name or email.');</script>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clients (user_id, name, email) VALUES (?, ?, ?)");
            if ($stmt->execute([$user_id, $client_name, $client_email])) {
                echo "<script>alert('Client added successfully!'); window.location.href='invoices.php';</script>";
            } else {
                echo "<script>alert('Error adding client.');</script>";
            }
        } catch (PDOException $e) {
            error_log('Client creation error: ' . $e->getMessage());
            echo "<script>alert('Database error adding client.');</script>";
        }
    }
}

// Handle invoice creation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_invoice'])) {
    $client_id = (int)$_POST['client_id'];
    $amount = (float)$_POST['amount'];
    $due_date = $_POST['due_date'];
    $csrf_token = $_POST['csrf_token'];

    if ($csrf_token !== $_SESSION['csrf_token']) {
        echo "<script>alert('CSRF validation failed.');</script>";
    } elseif ($client_id <= 0 || $amount <= 0 || empty($due_date)) {
        echo "<script>alert('Invalid invoice details.');</script>";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO invoices (user_id, client_id, amount, due_date) VALUES (?, ?, ?, ?)");
            if ($stmt->execute([$user_id, $client_id, $amount, $due_date])) {
                echo "<script>alert('Invoice created successfully!'); window.location.href='invoices.php';</script>";
            } else {
                echo "<script>alert('Error creating invoice.');</script>";
            }
        } catch (PDOException $e) {
            error_log('Invoice creation error: ' . $e->getMessage());
            echo "<script>alert('Database error creating invoice.');</script>";
        }
    }
}

// Fetch clients
try {
    $stmt = $pdo->prepare("SELECT * FROM clients WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Client fetch error: ' . $e->getMessage());
    $clients = [];
    echo "<script>alert('Error fetching clients.');</script>";
}

// Fetch invoices
try {
    $stmt = $pdo->prepare("SELECT i.*, c.name, c.email FROM invoices i JOIN clients c ON i.client_id = c.id WHERE i.user_id = ? ORDER BY i.created_at DESC");
    $stmt->execute([$user_id]);
    $invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Invoice fetch error: ' . $e->getMessage());
    $invoices = [];
    echo "<script>alert('Error fetching invoices.');</script>";
}

$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - QuickBooks Clone</title>
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
            box-sizing: border-box;
        }
        .btn {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 8px;
            transition: transform 0.3s, background 0.3s;
            margin: 5px;
            clip-path: polygon(10% 0, 100% 0, 90% 100%, 0 100%);
        }
        .btn:hover {
            transform: scale(1.05);
            background: #009688;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%);
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
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
            .form-container {
                padding: 15px;
            }
            table {
                font-size: 0.9em;
            }
            .navbar {
                flex-direction: column;
                text-align: center;
            }
            .navbar a {
                margin: 5px;
            }
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
        <h2>Add Client (if needed)</h2>
        <div class="form-container">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="create_client" value="1">
                <input type="text" name="client_name" placeholder="Client Name" required aria-label="Client Name">
                <input type="email" name="client_email" placeholder="Client Email" required aria-label="Client Email">
                <button type="submit" class="btn" aria-label="Add Client">Add Client</button>
            </form>
        </div>

        <h2>Create Invoice</h2>
        <?php if (empty($clients)): ?>
            <div class="warning">No clients found. Add a client above to create an invoice.</div>
        <?php else: ?>
            <div class="form-container">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="create_invoice" value="1">
                    <select name="client_id" required aria-label="Select Client">
                        <option value="">Select Client</option>
                        <?php foreach ($clients as $client): ?>
                            <option value="<?php echo $client['id']; ?>"><?php echo htmlspecialchars($client['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="amount" placeholder="Amount" step="0.01" min="0" required aria-label="Amount">
                    <input type="date" name="due_date" required aria-label="Due Date">
                    <button type="submit" class="btn" aria-label="Create Invoice">Create Invoice</button>
                </form>
            </div>
        <?php endif; ?>

        <h2>Your Invoices</h2>
        <table>
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Due Date</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($invoices)): ?>
                    <tr><td colspan="5" style="text-align: center;">No invoices yet. Create one above.</td></tr>
                <?php else: ?>
                    <?php foreach ($invoices as $invoice): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($invoice['name']); ?></td>
                            <td>$<?php echo number_format($invoice['amount'], 2); ?></td>
                            <td><?php echo ucfirst($invoice['status']); ?></td>
                            <td><?php echo $invoice['due_date']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($invoice['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
