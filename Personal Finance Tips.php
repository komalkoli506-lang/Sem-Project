<?php
session_start();
// Database configuration
$host = 'localhost';
$dbname = 'CPP';
$username = 'root';
$password = '';

// Create a database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
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
// Fetch finance tips from the database (Newest First)
$sql = "SELECT * FROM finance_tips ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Personal Finance Tips</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    /* Your existing CSS */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Roboto', sans-serif;
      background-color: #f8f9fa;
      color: #333;
      padding: 20px;
    }

    .container {
      max-width: 1200px;
      margin: 0 auto;
    }

    h1 {
      text-align: center;
      font-size: 36px;
      color:rgb(144, 13, 147);
      margin-bottom: 20px;
      animation: fadeIn 1.5s ease;
    }

    .intro {
      text-align: center;
      margin-bottom: 30px;
      font-size: 18px;
      color: #555;
    }

    .tips-section {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 20px;
      animation: fadeIn 1.5s ease-in-out;
    }

    .tip-card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
      padding: 20px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .tip-card:hover {
      transform: scale(1.05);
      box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.15);
    }

    .tip-icon {
      font-size: 40px;
      color: #6610f2;
      margin-bottom: 15px;
      text-align: center;
    }

    .tip-title {
      font-size: 22px;
      font-weight: 500;
      color: #007bff;
      margin-bottom: 10px;
    }

    .tip-text {
      font-size: 16px;
      line-height: 1.6;
      color: #555;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }

      to {
        opacity: 1;
      }
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
    <h1>Top Personal Finance Tips</h1>
    <div class="tips-section">

      <?php
      // Display finance tips from the database
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<div class='tip-card'>
                      <div class='tip-icon'>💡</div>
                      <div class='tip-title'>" . htmlspecialchars($row['title']) . "</div>
                      <div class='tip-text'>" . htmlspecialchars($row['description']) . "</div>
                    </div>";
        }
      } else {
        echo "<div class='tip-card'>No tips available at the moment.</div>";
      }
      ?>

    </div>
  </div>

</body>
<a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

</html>

<?php $conn->close(); ?>