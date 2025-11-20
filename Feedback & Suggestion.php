<?php
session_start();
$servername = "localhost";
$username = "root"; // Change if different
$password = ""; // Change if different
$dbname = "CPP";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
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
// Initialize variables
$successMessage = "";
$errorMessage = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = trim($_POST["name"]);
  $email = trim($_POST["email"]);
  $feedbackType = trim($_POST["feedbackType"]);
  $message = trim($_POST["message"]);

  // Validate inputs
  if (!empty($name) && !empty($email) && !empty($feedbackType) && !empty($message)) {
    // Use Prepared Statement to prevent SQL Injection
    $stmt = $conn->prepare("INSERT INTO feedback (name, email, feedback_type, message) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $feedbackType, $message);

    if ($stmt->execute()) {
      $successMessage = "Thank you! Your feedback has been submitted successfully.";
    } else {
      $errorMessage = "Error: Could not submit feedback. Please try again.";
    }

    $stmt->close();
  } else {
    $errorMessage = "All fields are required. Please fill out the form completely.";
  }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback & Suggestion Form</title>
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
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 20px;
    }

    .container {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
      padding: 30px;
      width: 100%;
      max-width: 600px;
      animation: fadeIn 1.5s ease-in-out;
    }

    h1 {
      text-align: center;
      margin-bottom: 20px;
      font-size: 34px;
      color:rgb(149, 10, 173);
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      font-weight: 500;
      color: #333;
      margin-bottom: 5px;
      display: block;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
      width: calc(100% - 20px);
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      font-size: 16px;
      outline: none;
    }

    .form-group textarea {
      resize: vertical;
      height: 100px;
    }

    .form-group select {
      height: 45px;
    }

    button {
      width: 100%;
      background: linear-gradient(90deg,rgb(174, 0, 225),rgb(163, 15, 158));
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
      background-color:rgb(142, 19, 153);
      transform: scale(1.05);
    }

    .message {
      text-align: center;
      font-weight: bold;
      margin-top: 20px;
      padding: 10px;
      border-radius: 5px;
    }

    .success {
      color: green;
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
    }

    .error {
      color: red;
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
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
    <h1>Feedback & Suggestion Form</h1>

    <!-- Display success or error message -->
    <?php if ($successMessage): ?>
      <div class="message success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
      <div class="message error"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <!-- Feedback Form -->
    <form action="" method="POST">
      <div class="form-group">
        <label for="name">Your Name</label>
        <input type="text" id="name" name="name" placeholder="Enter your name" required>
      </div>

      <div class="form-group">
        <label for="email">Your Email</label>
        <input type="email" id="email" name="email" placeholder="Enter your email" required>
      </div>

      <div class="form-group">
        <label for="feedbackType">Type of Feedback</label>
        <select id="feedbackType" name="feedbackType">
          <option value="suggestion">Suggestion</option>
          <option value="complaint">Complaint</option>
          <option value="query">Query</option>
          <option value="appreciation">Appreciation</option>
        </select>
      </div>

      <div class="form-group">
        <label for="message">Your Feedback / Suggestion</label>
        <textarea id="message" name="message" placeholder="Enter your feedback or suggestion" required></textarea>
      </div>

      <button type="submit">Submit Feedback</button>
    </form>
    <a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

  </div>

</body>

</html>