<?php
session_start();

// Database Connection
$servername = "localhost";
$username = "root"; // Change if needed
$password = ""; // Change if needed
$dbname = "CPP"; // Change to your database name

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login.php");
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
$user_id = $_SESSION['user_id']; // Get logged-in user's ID

// Handling Form Submission Securely
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $amount = $_POST['amount'];
    $date = $_POST['date'];

    $stmt = $conn->prepare("INSERT INTO income (user_id, income_category, income_amount, income_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $category, $amount, $date);

    if ($stmt->execute()) {
        echo "<script>alert('Income added successfully!');</script>";
    } else {
        echo "<script>alert('Error adding income: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Fetch User-Specific Incomes
$sql = "SELECT * FROM income WHERE user_id = ? ORDER BY income_date DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Management</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f8ff;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            background: white;
            padding: 20px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin: auto;
        }

        h1,
        h2 {
            text-align: center;
            color:rgb(171, 10, 177);
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            font-weight: bold;
        }

        input,
        button {
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background: linear-gradient(90deg,rgb(174, 0, 255),rgb(109, 30, 131));
            color: white;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color:rgb(131, 27, 166);
            color: white;
        }

        .no-data {
            text-align: center;
            color: gray;
            padding: 10px;
        }

        .backlink {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>

<body>

    <div class="container">
        <h1>Income Management</h1>

        <!-- Income Form -->
        <form method="POST" action="">
            <label>Category:</label>
            <input type="text" name="category" required>

            <label>Amount :</label>
            <input type="number" name="amount" step="0.01" required>

            <label>Date:</label>
            <input type="date" name="date" required>

            <button type="submit">Add Income</button>
        </form>

        <!-- Income History -->
        <h2>Income History</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Amount</th>
            </tr>

            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['income_date']}</td>
                        <td>{$row['income_category']}</td>
                        <td>{$row['income_amount']}</td>
                      </tr>";
                }
            } else {
                echo "<tr><td colspan='3' class='no-data'>No incomes found.</td></tr>";
            }
            $stmt->close();
            ?>

        </table>
    </div>

</body>
<a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

</html>

<?php $conn->close(); ?>