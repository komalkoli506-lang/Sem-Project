<?php
session_start();
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
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calculator Hub</title>
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
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      height: 100vh;
      padding: 20px;
    }

    .container {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.1);
      padding: 40px;
      width: 350px;
      text-align: center;
      position: relative;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .container:hover {
      transform: translateY(-10px);
      box-shadow: 0px 12px 25px rgba(0, 0, 0, 0.15);
    }

    h1 {
      margin-bottom: 25px;
      font-size: 36px;
      color:rgb(43, 225, 33);
      position: relative;
      padding-bottom: 10px;
    }

    h1::after {
      content: '';
      width: 80px;
      height: 4px;
      background-color:rgb(201, 16, 242);
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      border-radius: 2px;
    }

    a {
      display: block;
      font-size: 20px;
      color:rgb(131, 25, 163);
      text-decoration: none;
      margin: 15px 0;
      padding: 10px;
      border-radius: 8px;
      transition: color 0.3s ease, background-color 0.3s ease, transform 0.3s ease;
      background-color: #f8f9fa;
      box-shadow: 0px 2px 8px rgba(0, 0, 0, 0.05);
    }

    a:hover {
      color: #fff;
      background-color:rgb(128, 0, 179);
      transform: translateY(-3px);
    }

    .back-link {
      display: inline-block;
      margin-top: 30px;
      font-size: 16px;
      color: #6610f2;
      text-decoration: none;
      padding: 10px 20px;
      border: 2px solid #6610f2;
      border-radius: 50px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .back-link:hover {
      background-color: #6610f2;
      color: #fff;
    }

    /* Add subtle animation */
    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .container {
      animation: fadeIn 0.8s ease-in-out;
    }
  </style>
</head>

<body>

  <div class="container">
    <h1>Calculator Hub</h1>
    <a href="Sip Calculator.php">SIP Calculator</a>
     
    <a href="EMI Calculator.php">EMI Calculator</a>
    
    <!-- Add more calculators here as needed -->

  </div>

</body>
<a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

</html>