<?php
session_start();

$host = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password
$database = "CPP"; // Change this to your database name

$conn = new mysqli($host, $username, $password, $database);

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
require 'tcpdf/tcpdf.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $user_id = $_SESSION['user_id']; // Ensure the user is logged in
  $startDate = $_POST['reportPeriodStart'];
  $endDate = $_POST['reportPeriodEnd'];

  if (empty($startDate) || empty($endDate)) {
    die("Error: Please select start and end dates.");
  }

  // Fetch user-specific data from the income table
  $sql = "SELECT income_category, income_amount, income_date 
            FROM income 
            WHERE user_id = ? AND income_date BETWEEN ? AND ? 
            ORDER BY income_date ASC";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("iss", $user_id, $startDate, $endDate);
  $stmt->execute();
  $result = $stmt->get_result();

  // Create new PDF document
  $pdf = new TCPDF();
  $pdf->SetCreator(PDF_CREATOR);
  $pdf->SetAuthor('Income Tracker');
  $pdf->SetTitle('Income Report');
  $pdf->SetHeaderData('', 0, 'Income Report', "User ID: $user_id\nPeriod: $startDate to $endDate");

  $pdf->AddPage();
  $pdf->SetFont('helvetica', '', 12);

  $html = '<h2>Income Report</h2>';
  $html .= "<p>Period: $startDate to $endDate</p>";
  $html .= "<table border='1' cellpadding='5'><thead>
                <tr>
                    <th>Category</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
              </thead><tbody>";

  while ($row = $result->fetch_assoc()) {
    $html .= "<tr>
                    <td>{$row['income_category']}</td>
                    <td>{$row['income_amount']}</td>
                    <td>{$row['income_date']}</td>
                  </tr>";
  }

  $html .= '</tbody></table>';
  $pdf->writeHTML($html, true, false, true, false, '');

  // Save and Output the PDF
  $pdfFileName = "income_report_" . time() . ".pdf";
  $pdf->Output($pdfFileName, 'D'); // 'D' forces download

  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Income Report Generation</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f0f8ff;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
      margin: 0;
    }

    .container {
      background-color: white;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0px 6px 18px rgba(0, 0, 0, 0.15);
      width: 50%;
      max-width: 600px;
    }

    h1 {
      text-align: center;
      color:rgb(120, 14, 139);
      font-size: 28px;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: bold;
      font-size: 16px;
      margin-bottom: 6px;
    }

    .form-group input {
      width: 100%;
      padding: 12px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 16px;
      transition: all 0.3s ease-in-out;
    }

    .form-group input:focus {
      border-color:rgb(109, 7, 116);
      outline: none;
      box-shadow: 0px 0px 8px rgba(166, 28, 171, 0.4);
    }

    button {
      width: 100%;
      padding: 14px;
      background:rgb(119, 11, 133);
      color: white;
      border: none;
      cursor: pointer;
      border-radius: 6px;
      font-size: 18px;
      transition: 0.3s ease-in-out;
    }

    button:hover {
      background:rgb(130, 12, 128);
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
    <h1>Income Report</h1>
    <form method="POST">
      <div class="form-group">
        <label for="reportPeriodStart">Start Date:</label>
        <input type="date" id="reportPeriodStart" name="reportPeriodStart">
      </div>
      <div class="form-group">
        <label for="reportPeriodEnd">End Date:</label>
        <input type="date" id="reportPeriodEnd" name="reportPeriodEnd">
      </div>
      <button type="submit">Generate Report</button>
    </form>
    <a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

  </div>
</body>

</html>