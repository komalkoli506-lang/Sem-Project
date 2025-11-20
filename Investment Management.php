<?php
session_start();

// Database Connection
$host = "localhost";
$user = "root";
$password = "";
$database = "CPP";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php"); // Redirect to login page if not logged in
    exit();
}
// Session timeout (e.g., 30 minutes)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: Login.php");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

// Optional: Regenerate session ID to prevent session fixation
session_regenerate_id(true);
$user_id = $_SESSION['user_id'];

// Handle Investment Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['investmentName'] === "Other" ? $_POST['otherInvestment'] : $_POST['investmentName'];
    $amount = $_POST['investmentAmount'];
    $date = $_POST['investmentDate'];

    $stmt = $conn->prepare("INSERT INTO investment (user_id, investment_name, amount, date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isds", $user_id, $name, $amount, $date);

    if ($stmt->execute()) {
        header("Location: Investment Management.php"); // Refresh page to show new investment
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch Investment Summary (User-Specific)
$summary = $conn->prepare("SELECT SUM(amount) AS totalInvestment, COUNT(investment_id) AS totalTransactions FROM investment WHERE user_id = ?");
$summary->bind_param("i", $user_id);
$summary->execute();
$summaryResult = $summary->get_result();
$summaryData = $summaryResult->fetch_assoc();
$summary->close();

// Fetch Investment History (User-Specific)
$history = $conn->prepare("SELECT * FROM investment WHERE user_id = ? ORDER BY date DESC");
$history->bind_param("i", $user_id);
$history->execute();
$historyResult = $history->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Investment Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f8ff;
            color: #333;
            text-align: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            max-width: 800px;
            margin: auto;
        }

        h1 {
            color:rgb(171, 10, 177);
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            text-align: left;
        }

        .form-group label {
            font-weight: 500;
        }

        input,
        select {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            width: 100%;
        }

        button {
            background: linear-gradient(90deg,rgb(174, 0, 255),rgb(109, 30, 131));
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background-color:rgb(174, 0, 255);
            transform: scale(1.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }

        th {
            color: black;
        }

        td {
            color: green;
            font-weight: bold;
        }

        .logout {
            margin-top: 20px;
            display: inline-block;
            color: red;
            text-decoration: none;
            font-weight: bold;
        }

        .logout:hover {
            text-decoration: underline;
        }

        /* Hide custom input field initially */
        #otherInvestmentGroup {
            display: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Investment Management</h1>

        <!-- Add Investment Form -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="investmentName">Investment Name:</label>
                <select id="investmentName" name="investmentName" onchange="toggleOtherInput()">
                    <option value="Stocks">Stocks</option>
                    <option value="Bonds">Bonds</option>
                    <option value="Mutual Funds">Mutual Funds</option>
                    <option value="Real Estate">Real Estate</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group" id="otherInvestmentGroup">
                <label for="otherInvestment">Enter Investment Name:</label>
                <input type="text" id="otherInvestment" name="otherInvestment">
            </div>
            <div class="form-group">
                <label for="investmentAmount">Investment Amount ($):</label>
                <input type="number" id="investmentAmount" name="investmentAmount" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="investmentDate">Date:</label>
                <input type="date" id="investmentDate" name="investmentDate" required>
            </div>
            <button type="submit">Add Investment</button>
        </form>

        <!-- Investment Overview -->
        <h2>Investment Overview</h2>
        <table>
            <tr>
                <th>Total Investments</th>
                <td><?= number_format($summaryData['totalInvestment'], 2) ?></td>
            </tr>
            <tr>
                <th>Total Transactions</th>
                <td><?= $summaryData['totalTransactions'] ?></td>
            </tr>
        </table>

        <!-- Investment History -->
        <h2>Investment History</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $historyResult->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['date'] ?></td>
                        <td><?= htmlspecialchars($row['investment_name']) ?></td>
                        <td><?= number_format($row['amount'], 2) ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script>
        function toggleOtherInput() {
            var selectBox = document.getElementById("investmentName");
            var otherGroup = document.getElementById("otherInvestmentGroup");
            var otherInput = document.getElementById("otherInvestment");

            if (selectBox.value === "Other") {
                otherGroup.style.display = "block";
                otherInput.required = true;
            } else {
                otherGroup.style.display = "none";
                otherInput.required = false;
            }
        }
    </script>
</body>
<a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

</html>

<?php
$history->close();
$conn->close();
?>