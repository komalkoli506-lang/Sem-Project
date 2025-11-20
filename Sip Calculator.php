<?php
session_start();
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SIP Calculator</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
      width: 400px;
      animation: fadeIn 1.5s ease-in-out;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 34px;
      color:rgb(156, 18, 183);
    }

    label {
      font-weight: 500;
      color: #333;
      margin-bottom: 5px;
      display: inline-block;
    }

    input[type="text"],
    input[type="range"] {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ccc;
      border-radius: 5px;
      transition: border-color 0.3s ease;
    }

    input[type="text"]:focus {
      border-color: #007bff;
      box-shadow: 0 0 8px rgba(0, 123, 255, 0.3);
      outline: none;
    }

    button {
      width: 100%;
      background: linear-gradient(90deg, #007bff, #6610f2);
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

    .result {
      margin-top: 20px;
    }

    .result-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    .result-table th,
    .result-table td {
      text-align: left;
      padding: 8px;
      border-bottom: 1px solid #ddd;
    }

    .result-table th {
      color: black;
    }

    .result-table td {
      color: green;
      font-weight: bold;
    }

    .chart-container {
      margin-top: 20px;
      width: 100%;
      height: 400px;
      /* Ensure the chart container has a height */
    }

    .chart-label {
      text-align: center;
      margin-top: 10px;
      font-weight: bold;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
    }

    .back-link {
      display: block;
      margin-top: 20px;
      font-size: 16px;
      color: #6610f2;
      text-decoration: none;
      text-align: center;
    }

    .back-link:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <div class="container">
    <h1>SIP Calculator</h1>

    <label for="sipAmount">Monthly SIP Amount:</label>
    <input type="range" id="sipAmountRange" min="1000" max="50000" value="10000" oninput="updateSIPAmount()">
    <input type="text" id="sipAmount" value="10000" step="100" oninput="updateSIPRange()">

    <label for="duration">SIP Duration (In Months):</label>
    <input type="range" id="durationRange" min="12" max="240" value="120" oninput="updateDuration()">
    <input type="text" id="duration" value="120" step="1" oninput="updateDurationRange()">

    <label for="return">Expected Return (%):</label>
    <input type="range" id="returnRange" min="5" max="20" value="14" oninput="updateReturnRate()">
    <input type="text" id="return" value="14" step="1" oninput="updateReturnRange()">

    <button onclick="calculateSIP()">Calculate</button>

    <div class="result">
      <table class="result-table">
        <tr>
          <th>Monthly SIP Amount</th>
          <td id="resultSIPAmount"></td>
        </tr>
        <tr>
          <th>SIP Duration (In Months)</th>
          <td id="resultDuration"></td>
        </tr>
        <tr>
          <th>Expected Return (%)</th>
          <td id="resultReturn"></td>
        </tr>
        <tr>
          <th>Total SIP Amount Invested</th>
          <td id="resultTotalSIP"></td>
        </tr>
        <tr>
          <th>Total Growth</th>
          <td id="resultTotalGrowth"></td>
        </tr>
        <tr>
          <th>Total Future Value</th>
          <td id="resultTotalFutureValue"></td>
        </tr>
      </table>
    </div>

    <div class="chart-container">
      <canvas id="sipChart"></canvas>
    </div>
    <div class="chart-label">Investment Amount vs. Growth Amount</div>

    <a class="back-link" href="cout.php">Back to Calculator Hub</a>
  </div>

  <script>
    let sipChart = null;

    function updateSIPAmount() {
      document.getElementById("sipAmount").value = document.getElementById("sipAmountRange").value;
    }

    function updateSIPRange() {
      document.getElementById("sipAmountRange").value = document.getElementById("sipAmount").value;
    }

    function updateDuration() {
      document.getElementById("duration").value = document.getElementById("durationRange").value;
    }

    function updateDurationRange() {
      document.getElementById("durationRange").value = document.getElementById("duration").value;
    }

    function updateReturnRate() {
      document.getElementById("return").value = document.getElementById("returnRange").value;
    }

    function updateReturnRange() {
      document.getElementById("returnRange").value = document.getElementById("return").value;
    }

    function calculateSIP() {
      const sipAmount = parseFloat(document.getElementById("sipAmount").value);
      const duration = parseFloat(document.getElementById("duration").value);
      const returnRate = parseFloat(document.getElementById("return").value);

      const totalSIP = sipAmount * duration;
      const totalGrowth = totalSIP * (Math.pow((1 + (returnRate / 100)), (duration / 12)) - 1);
      const totalFutureValue = totalSIP + totalGrowth;

      document.getElementById("resultSIPAmount").textContent = sipAmount.toFixed(2);
      document.getElementById("resultDuration").textContent = duration.toFixed(2);
      document.getElementById("resultReturn").textContent = returnRate.toFixed(2);
      document.getElementById("resultTotalSIP").textContent = totalSIP.toFixed(2);
      document.getElementById("resultTotalGrowth").textContent = totalGrowth.toFixed(2);
      document.getElementById("resultTotalFutureValue").textContent = totalFutureValue.toFixed(2);

      const ctx = document.getElementById('sipChart').getContext('2d');

      if (sipChart) {
        sipChart.destroy();
      }

      sipChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['Total SIP Amount', 'Total Growth'],
          datasets: [{
            label: 'SIP Values',
            data: [totalSIP, totalGrowth],
            backgroundColor: ['#007bff', '#6610f2'],
            borderColor: ['#007bff', '#6610f2'],
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            x: {
              beginAtZero: true
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return value.toLocaleString();
                }
              }
            }
          }
        }
      });
    }
  </script>
</body>

</html>