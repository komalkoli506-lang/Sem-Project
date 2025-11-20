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
  <title>EMI Calculator</title>
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
      height: 100vh;
    }

    .container {
      width: 500px;
      height: 120vh;
      margin: 0 auto;
      padding: 30px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
    }

    h1 {
      font-size: 34px;
      color: #6610f2;
      text-align: center;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-size: 16px;
      margin-bottom: 8px;
    }

    .form-group input[type="number"],
    .form-group input[type="range"] {
      width: 100%;
      padding: 10px;
      border-radius: 8px;
      border: 1px solid #ccc;
      font-size: 16px;
    }

    .form-group input[type="range"] {
      width: calc(100% - 24px);
      margin: 10px 0;
    }

    button {
      padding: 12px 20px;
      background: linear-gradient(45deg, #007bff, #6610f2);
      border: none;
      color: white;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      margin-top: 10px;
      width: 100%;
      transition: background-color 0.3s ease;
    }

    button:hover {
      background-color: #0056b3;
    }

    .result {
      margin-top: 20px;
      font-size: 18px;
    }

    .result div {
      margin-bottom: 10px;
      color: green;
    }

    .chart-container {
      margin-top: 30px;
      width: 100%;
      height: 400px;
    }

    .back-btn {
      display: inline-block;
      margin-top: 0px;
      background-color: #6610f2;
      color: white;
      padding: 10px 20px;
      text-decoration: none;
      border-radius: 8px;
      text-align: center;
      transition: background-color 0.3s ease;
    }

    .back-btn:hover {
      background-color: #0056b3;
    }
  </style>
</head>

<body>

  <div class="container">
    <h1>EMI Calculator</h1>

    <div class="form-group">
      <label for="loanAmount">Loan Amount (₹)</label>
      <input type="range" id="loanAmountRange" min="10000" max="1000000" step="5000" value="50000"
        oninput="document.getElementById('loanAmount').value = this.value;">
      <input type="number" id="loanAmount" value="50000" min="10000" max="1000000"
        oninput="document.getElementById('loanAmountRange').value = this.value;">
    </div>

    <div class="form-group">
      <label for="loanDuration">Loan Duration (Months)</label>
      <input type="range" id="loanDurationRange" min="12" max="360" step="1" value="120"
        oninput="document.getElementById('loanDuration').value = this.value;">
      <input type="number" id="loanDuration" value="120" min="12" max="360"
        oninput="document.getElementById('loanDurationRange').value = this.value;">
    </div>

    <div class="form-group">
      <label for="interestRate">Interest Rate (%)</label>
      <input type="range" id="interestRateRange" min="1" max="20" step="0.1" value="7.5"
        oninput="document.getElementById('interestRate').value = this.value;">
      <input type="number" id="interestRate" value="7.5" min="1" max="20" step="0.1"
        oninput="document.getElementById('interestRateRange').value = this.value;">
    </div>

    <button onclick="calculateEMI()">Calculate EMI</button>

    <!-- Result Section -->
    <div id="result" class="result">
      <div id="loanAmountResult">Loan Amount (₹): </div>
      <div id="loanDurationResult">Duration (Months): </div>
      <div id="interestRateResult">Interest Rate (%): </div>
      <div id="monthlyEmiResult">Monthly EMI (₹): </div>
      <div id="totalInterestResult">Total Interest (₹): </div>
      <div id="totalPaymentResult">Total Payment (₹): </div>
    </div>

    <!-- Loan Repayment Chart -->
    <div class="chart-container">
      <canvas id="emiChart"></canvas>
    </div>

    <!-- Back to Dashboard button -->
    <a href="cout.php" class="back-btn">Back to Calculator Hub</a>
  </div>

  <script>
    function calculateEMI() {
      // Fetch input values
      let loanAmount = parseFloat(document.getElementById('loanAmount').value);
      let loanDuration = parseInt(document.getElementById('loanDuration').value);
      let interestRate = parseFloat(document.getElementById('interestRate').value) / 100 / 12;

      // EMI Calculation Formula
      let emi = (loanAmount * interestRate * Math.pow(1 + interestRate, loanDuration)) / (Math.pow(1 + interestRate, loanDuration) - 1);
      let totalPayment = emi * loanDuration;
      let totalInterest = totalPayment - loanAmount;

      // Update result section
      document.getElementById('loanAmountResult').textContent = `Loan Amount (₹): ${loanAmount.toFixed(2)}`;
      document.getElementById('loanDurationResult').textContent = `Duration (Months): ${loanDuration}`;
      document.getElementById('interestRateResult').textContent = `Interest Rate (%): ${(interestRate * 12 * 100).toFixed(2)}`;
      document.getElementById('monthlyEmiResult').textContent = `Monthly EMI (₹): ${emi.toFixed(2)}`;
      document.getElementById('totalInterestResult').textContent = `Total Interest (₹): ${totalInterest.toFixed(2)}`;
      document.getElementById('totalPaymentResult').textContent = `Total Payment (₹): ${totalPayment.toFixed(2)}`;

      // Update Loan Repayment Graph
      let principalData = [];
      let interestData = [];
      let remainingLoanAmount = loanAmount;
      for (let i = 1; i <= loanDuration; i++) {
        let monthlyInterest = remainingLoanAmount * interestRate;
        let monthlyPrincipal = emi - monthlyInterest;
        remainingLoanAmount -= monthlyPrincipal;
        principalData.push(monthlyPrincipal.toFixed(2));
        interestData.push(monthlyInterest.toFixed(2));
      }

      const ctx = document.getElementById('emiChart').getContext('2d');
      if (window.emiChart) window.emiChart.destroy();  // Destroy previous chart instance
      window.emiChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: Array.from({ length: loanDuration }, (_, i) => `Month ${i + 1}`),
          datasets: [
            {
              label: 'Principal Repayment (₹)',
              data: principalData,
              backgroundColor: 'rgba(0, 123, 255, 0.6)',
              borderColor: '#007bff',
              borderWidth: 1,
            },
            {
              label: 'Interest Repayment (₹)',
              data: interestData,
              backgroundColor: 'rgba(220, 53, 69, 0.6)',
              borderColor: '#dc3545',
              borderWidth: 1,
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            x: { stacked: true },
            y: { stacked: true }
          }
        }
      });

      // Clear input fields after calculation
      document.getElementById('loanAmount').value = '';
      document.getElementById('loanDuration').value = '';
      document.getElementById('interestRate').value = '';
      document.getElementById('loanAmountRange').value = 50000;
      document.getElementById('loanDurationRange').value = 120;
      document.getElementById('interestRateRange').value = 7.5;
    }
  </script>

</body>

</html>