<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "CPP";

// Database Connection
$conn = new mysqli($servername, $username, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) 
{
    header("Location: Login.php");
    exit();
}

// Session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: Login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time
session_regenerate_id(true);

$user_id = $_SESSION['user_id'];

// Fetch User Name
$userQuery = $conn->prepare("SELECT name FROM users WHERE user_id = ?");
$userQuery->bind_param("i", $user_id);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userRow = $userResult->fetch_assoc();
$userName = htmlspecialchars($userRow['name'] ?? "User");

// Fetch Totals
$totalsQuery = $conn->prepare("
    SELECT 
        (SELECT COALESCE(SUM(amount), 0) FROM investment WHERE user_id = ?) AS totalInvestment,
        (SELECT COALESCE(SUM(income_amount), 0) FROM income WHERE user_id = ?) AS totalIncome,
        (SELECT COALESCE(SUM(expense_amount), 0) FROM expense WHERE user_id = ?) AS totalExpense
");
$totalsQuery->bind_param("iii", $user_id, $user_id, $user_id);
$totalsQuery->execute();
$totalsResult = $totalsQuery->get_result()->fetch_assoc();

$totalInvestment = $totalsResult['totalInvestment'];
$totalIncome = $totalsResult['totalIncome'];
$totalExpense = $totalsResult['totalExpense'];

// Portfolio Allocation
$portfolioQuery = $conn->prepare("SELECT investment_name, SUM(amount) AS total FROM investment WHERE user_id = ? GROUP BY investment_name");
$portfolioQuery->bind_param("i", $user_id);
$portfolioQuery->execute();
$portfolioResult = $portfolioQuery->get_result();
$portfolioData = [];
while ($row = $portfolioResult->fetch_assoc()) {
    $portfolioData[] = $row;
}

// Monthly Expenses
$monthlyExpensesQuery = $conn->prepare("SELECT MONTH(expense_date) AS month, SUM(expense_amount) AS total FROM expense WHERE user_id = ? GROUP BY MONTH(expense_date)");
$monthlyExpensesQuery->bind_param("i", $user_id);
$monthlyExpensesQuery->execute();
$monthlyExpensesResult = $monthlyExpensesQuery->get_result();
$monthlyExpensesData = [];
while ($row = $monthlyExpensesResult->fetch_assoc()) {
    $monthlyExpensesData[] = $row;
}

// Recent Transactions
$recent_transactions = [];

$queries = [
    "SELECT 'Income' AS type, income_amount AS amount, income_date AS date FROM income WHERE user_id = ? ORDER BY income_date DESC LIMIT 5",
    "SELECT 'Expense' AS type, expense_amount AS amount, expense_date AS date FROM expense WHERE user_id = ? ORDER BY expense_date DESC LIMIT 5",
    "SELECT 'Investment' AS type, amount, date FROM investment WHERE user_id = ? ORDER BY date DESC LIMIT 5"
];

foreach ($queries as $query) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $recent_transactions[] = $row;
    }
}

// Sort all by date
usort($recent_transactions, function ($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Portfolio & Expense Tracker</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(to right, #e0eafc,rgb(125, 34, 171));
            color: #333;
        }

header {
            background: linear-gradient(90deg, rgb(170, 62, 220), rgb(117, 10, 155));
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        header h1 {
            font-size: 24px;
        }

        .user-profile a {
            color: white;
            margin-left: 15px;
            text-decoration: none;
            padding: 6px 12px;
            background-color: rgba(123, 5, 159, 0.2);
            border-radius: 8px;
        }

        .user-profile a:hover {
            background-color: rgba(115, 12, 143, 0.4);
        }

       
        .sidebar.active {
            left: 30px;
        }
        .sidebar ul { list-style: none; }
        .sidebar li { margin-bottom: 20px; }

        .container {
            display: flex;
            position: relative;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            padding: 30px;
            position: absolute;
            top: 15px;
            left: -270px;
            transition: left 0.20s ease;
            height: calc(100vh - 100px);
            border-radius: 10px;
            overflow-y: auto;
        }

          
        .sidebar a {
                      color: white;
                    text-decoration: none;
    
                    font-weight: 1000;
                    display: inline-block;
                 transition: transform 0.2s ease, background-color 0.2s ease;
                     padding: 10px;
                     border-radius: 10px;
                }
            #toggleSidebar {
                font-size: 24px;
                background: transparent;
                color: white;
                border: none;
                cursor: pointer;
                
            }

            .sidebar a:hover {
            transform: scale(1.4);
             background-color: rgba(255, 255, 255, 0.1);
    
         }

        .sidebar a::before {
            content: '📊';
            margin-right: 10px;
        }

        .dashboard {
    flex-grow: 1;
    margin-left: 50px; /* Shift it slightly to right by default */
    padding: 30px;
    background-color: white;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: margin-left 0.3s ease;
}

/* When sidebar is active, shift dashboard more */
.sidebar.active ~ .dashboard {
    margin-left: 300px; /* Push dashboard to right when sidebar opened */
}
        .overview-panels {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .panel {
            background-color: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
            flex-grow: 1;
            margin-right: 25px;
            transition: transform 0.3s ease;
        }

        .panel:hover {
            transform: translateY(-10px);
        }

        .panel:last-child { margin-right: 0; }

        .charts-section {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }

        .chart {
            background-color: #fff;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            width: 40%;
        }

        .recent-transactions table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            margin-top: 30px;
        }

        .recent-transactions th,
        .recent-transactions td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
        }

        footer {
            background: linear-gradient(90deg, rgb(187, 49, 212), rgb(115, 20, 152));
            color: white;
            text-align: center;
            padding: 15px;
            margin-top: 40px;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<header>
    <button id="toggleSidebar">☰</button>
    <h1>Portfolio & Expense Tracker Dashboard</h1>
    <div class="user-profile">
        <span>Welcome, <?php echo $userName; ?></span>
        <a href="Profile.php">Profile</a>
        <a href="logout.php">Logout</a>
    </div>
</header>

<div class="container">
    <!-- Sidebar -->
    <nav class="sidebar">
        <ul>
            <li><a href="#dashboard">Dashboard</a></li>
            <li><a href="Investment Management.php">Investment  Management</a></li>
            <li><a href="Expense Management.php">Expense Management</a></li>
            <li><a href="Income Management.php">Income Management</a></li>
            <li><a href="Goal.php">Goal</a></li>
            <li><a href="Investment Report Generation.php">Investment Report Generation</a></li>
            <li><a href="Expense Report Generation.php">Expense Report Generation</a></li>
            <li><a href="Income Report Generation.php">Income Report Generation</a></li>
            <li><a href="Calculators.php">Calculators</a></li>
            <li><a href="Feedback & Suggestion.php">Feedback & Suggestion</a></li>
            <li><a href="Personal Finance Tips.php">Personal Finance Tips</a></li>
        </ul>
    </nav>

    <!-- Dashboard -->
    <main class="dashboard">
        <section class="financial-overview">
            <h2>Financial Overview</h2>
            <div class="overview-panels">
                <div class="panel">
                    <h3>Total Investment</h3>
                    <p><?php echo number_format($totalInvestment, 2); ?> INR</p>
                </div>
                <div class="panel">
                    <h3>Total Income</h3>
                    <p><?php echo number_format($totalIncome, 2); ?> INR</p>
                </div>
                <div class="panel">
                    <h3>Total Expense</h3>
                    <p><?php echo number_format($totalExpense, 2); ?> INR</p>
                </div>
            </div>
        </section>

        <section class="charts-section">
            <div class="chart">
                <h3>Portfolio Allocation</h3>
                <canvas id="portfolioChart"></canvas>
            </div>
            <div class="chart">
                <h3>Monthly Expenses</h3>
                <canvas id="expensesChart"></canvas>
            </div>
        </section>

        <section class="recent-transactions">
            <h2>Recent Transactions</h2>
            <table>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
                <?php foreach ($recent_transactions as $transaction): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($transaction['type']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['amount']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>
</div>

<footer>
    <p>&copy; 2024 Portfolio Management & Expense Tracker. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const toggleButton = document.getElementById('toggleSidebar');
    const sidebar = document.querySelector('.sidebar');

    toggleButton.addEventListener('click', () => {
        sidebar.classList.toggle('active');
    });

    new Chart(document.getElementById('portfolioChart'), {
        type: 'pie',
        data: {
            labels: <?php echo json_encode(array_column($portfolioData, 'investment_name')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($portfolioData, 'total')); ?>,
                backgroundColor: ['#f87171', '#60a5fa', '#34d399', '#facc15']
            }]
        }
    });

    new Chart(document.getElementById('expensesChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($monthlyExpensesData, 'month')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($monthlyExpensesData, 'total')); ?>,
                backgroundColor: '#60a5fa'
            }]
        }
    });
</script>
</body>
</html>