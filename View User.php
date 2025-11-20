<?php
session_start();
// Database connection
$servername = "localhost";
$username = "root"; // Change if necessary
$password = ""; // Change if necessary
$database = "CPP"; // Change to your database

$conn = new mysqli($servername, $username, $password, $database);

//Check connection
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
// Fetch users data
$sql = "SELECT user_id, name, username, email, phone, address, dob, aadhar_no, pan_no FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<style>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>View Users</title><link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
  }

  body {
    font-family: 'Roboto', sans-serif;
    background-color: #f4f4f9;
    color: #333;
  }

  .container {
    max-width: 1000px;
    margin: 30px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    overflow-x: auto;
    /* Enables horizontal scrolling */
  }

  h1 {
    font-size: 32px;
    color:rgb(135, 15, 182);
    margin-bottom: 20px;
    text-align: center;
  }

  .user-list {
    margin-top: 20px;
  }

  .user-list table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
    /* Allows columns to adjust based on content */
  }

  .user-list th,
  .user-list td {
    padding: 12px 10px;
    text-align: left;
    border-bottom: 1px solid #ddd;
    white-space: nowrap;
    /* Prevents breaking */
  }

  .user-list th {
    background-color:rgb(126, 17, 172);
    color: white;
  }

  .user-list tr:hover {
    background-color: #f1f1f1;
  }

  /* Column width adjustments */
  .user-list th:nth-child(1),
  /* User ID */
  .user-list td:nth-child(1) {
    width: 5%;
  }

  .user-list th:nth-child(2),
  /* Name */
  .user-list td:nth-child(2) {
    width: 10%;
  }

  .user-list th:nth-child(3),
  /* Username */
  .user-list td:nth-child(3) {
    width: 12%;
  }

  .user-list th:nth-child(4),
  /* Email */
  .user-list td:nth-child(4) {
    width: 18%;
  }

  .user-list th:nth-child(5),
  /* Phone */
  .user-list td:nth-child(5) {
    width: 10%;
  }

  .user-list th:nth-child(6),
  /* Address */
  .user-list td:nth-child(6) {
    width: 18%;
    white-space: normal;
    /* Allows wrapping */
  }

  .user-list th:nth-child(7),
  /* DOB */
  .user-list td:nth-child(7) {
    width: 10%;
  }

  .user-list th:nth-child(8),
  /* Aadhar No */
  .user-list td:nth-child(8) {
    width: 15%;
    white-space: normal;
    /* Ensures full visibility */
  }

  .user-list th:nth-child(9),
  /* PAN No */
  .user-list td:nth-child(9) {
    width: 10%;
    white-space: normal;
    /* Ensures full visibility */
  }

  /* Responsive Design */
  @media (max-width: 768px) {
    .container {
      width: 95%;
      padding: 10px;
    }

    .user-list table {
      display: block;
      overflow-x: auto;
      /* Enables scrolling */
    }

    .user-list th,
    .user-list td {
      font-size: 14px;
      padding: 10px 5px;
      white-space: nowrap;
      /* Prevents text from wrapping on small screens */
    }
  }

  .backlink {
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>
</>

<body>

  <div class="container">
    <h1>View Users</h1>

    <!-- User List -->
    <div class="user-list">
      <table>
        <thead>
          <tr>
            <th>User ID</th>
            <th>Name</th>
            <th>Username</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Address</th>
            <th>Date of Birth</th>
            <th>Aadhar No</th>
            <th>PAN No</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              echo "<tr>
                          <td>{$row['user_id']}</td>
                          <td>{$row['name']}</td>
                          <td>{$row['username']}</td>
                          <td>{$row['email']}</td>
                          <td>{$row['phone']}</td>
                          <td>{$row['address']}</td>
                          <td>{$row['dob']}</td>
                          <td>{$row['aadhar_no']}</td>
                          <td>{$row['pan_no']}</td>
                        </tr>";
            }
          } else {
            echo "<tr><td colspan='9' style='text-align:center;'>No users found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
<a href="adminout.php" class="backlink">← Back to Admin</a>

</html>

<?php
$conn->close();
?>