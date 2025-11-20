<?php
session_start();
$servername = "localhost";  // Change if using a remote database
$username = "root";         // Your database username
$password = "";             // Your database password
$database = "CPP"; // Replace with your actual database name

// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
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

$user_id = $_SESSION['user_id'];

// Insert goal into database
if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $goalName = $_POST['goalName'];
  $goalAmount = $_POST['goalAmount'];
  $availableAmount = $_POST['availableAmount'];

  // Check if goal already exists for this user
  $checkQuery = "SELECT * FROM goal WHERE user_id = ? AND goal_name = ?";
  $stmt = $conn->prepare($checkQuery);
  $stmt->bind_param("is", $user_id, $goalName);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    // Update available amount if goal exists
    $updateQuery = "UPDATE goal SET available_amount = available_amount + ? WHERE user_id = ? AND goal_name = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("dis", $availableAmount, $user_id, $goalName);
    $stmt->execute();
  } else {
    // Insert new goal
    $insertQuery = "INSERT INTO goal (user_id, goal_name, goal_amount, available_amount) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("isdd", $user_id, $goalName, $goalAmount, $availableAmount);
    $stmt->execute();
  }
  $stmt->close();
  header("Location: goal.php");
  exit();
}

// Fetch user goals
$goals = [];
$query = "SELECT * FROM goal WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
  $goals[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Goal</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    /* Keep design unchanged */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f0f8ff;
      color: #333;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      height: 100vh;
      overflow-y: auto;
      padding: 20px;
    }

    .container {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
      width: 100%;
      max-width: 1200px;
      animation: fadeIn 1.5s ease-in-out;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 34px;
      color:rgb(113, 14, 156);
    }

    h2 {
      margin-top: 20px;
      font-size: 20px;
      color:rgb(113, 14, 156);
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      font-weight: 500;
      color: #333;
      margin-bottom: 5px;
      display: block;
    }

    .form-group input {
      width: calc(100% - 20px);
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    button {
      width: 100%;
      background: linear-gradient(90deg,rgb(79, 9, 94),rgb(66, 9, 86));
      color: white;
      padding: 12px 20px;
      text-align: center;
      font-size: 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      transition: background-color 0.3s ease, transform 0.2s ease;
    }

    button:hover {
      background-color: #0056b3;
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
      color: #333;
    }

    .chart-container {
      margin-top: 30px;
      width: 100%;
      max-width: 600px;
      height: 400px;
    }

    progress {
      width: 100%;
      height: 20px;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>Goal</h1>

    <!-- Goal Input Form -->
    <form method="POST">
      <div class="form-group">
        <label for="goalName">Goal Name:</label>
        <input type="text" id="goalName" name="goalName" placeholder="Enter your goal" required>
      </div>
      <div class="form-group">
        <label for="goalAmount">Goal Amount ($):</label>
        <input type="number" id="goalAmount" name="goalAmount" placeholder="Enter the goal amount" required>
      </div>
      <div class="form-group">
        <label for="availableAmount">Available Amount ($):</label>
        <input type="number" id="availableAmount" name="availableAmount" placeholder="Enter the available amount"
          required>
      </div>
      <button type="submit">Add Goal</button>
    </form>

    <!-- Goal Overview -->
    <h2>Your Goals</h2>
    <table>
      <thead>
        <tr>
          <th>Goal Name</th>
          <th>Goal Amount</th>
          <th>Available Amount</th>
        </tr>
      </thead>
      <tbody id="goalTable">
        <?php foreach ($goals as $goal): ?>
          <tr>
            <td><?= htmlspecialchars($goal['goal_name']) ?></td>
            <td><?= number_format($goal['goal_amount'], 2) ?></td>
            <td><?= number_format($goal['available_amount'], 2) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <!-- Progress Chart -->
    <div class="chart-container">
      <canvas id="goalChart"></canvas>
    </div>

    <a class="back-link" href="logouthomepage.php">Back to Homepage</a>
  </div>

  <script>
    var goals = <?= json_encode($goals) ?>;

    function updateGoalChart() {
      var ctx = document.getElementById('goalChart').getContext('2d');
      var goalNames = goals.map(goal => goal.goal_name);
      var availableData = goals.map(goal => (goal.available_amount / goal.goal_amount) * 100);

      new Chart(ctx, {
        type: 'bar',
        data: { labels: goalNames, datasets: [{ label: 'Available vs Goal (%)', data: availableData, backgroundColor: '#6610f2' }] },
        options: { responsive: true, scales: { y: { beginAtZero: true, max: 100 } } }
      });
    }
    updateGoalChart();
  </script>
</body>

</html>