<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$database = "CPP";

$conn = new mysqli($servername, $username, $password, $database);
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
// Handle Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
  $stmt = $conn->prepare("UPDATE users SET name=?, username=?, email=?, phone=?, address=? WHERE user_id=?");
  $stmt->bind_param("sssssi", $_POST['name'], $_POST['username'], $_POST['email'], $_POST['phone'], $_POST['address'], $_POST['user_id']);
  if ($stmt->execute()) {
    $message = "User updated successfully!";
  } else {
    $message = "Error updating user.";
  }
}

// Handle Delete Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
  $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
  $stmt->bind_param("i", $_POST['user_id']);
  if ($stmt->execute()) {
    $message = "User deleted successfully!";
  } else {
    $message = "Error deleting user.";
  }
}

// Fetch Users
$sql = "SELECT user_id, name, username, email, phone, address,  FROM users";
$result = $conn->query($sql);
$users = [];
while ($row = $result->fetch_assoc()) {
  $users[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <style>
    * {
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
      margin: 20px auto;
      padding: 20px;
    }

    h1 {
      font-size: 32px;
      color:rgb(112, 9, 128);
      margin-bottom: 20px;
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th,
    td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color:rgb(106, 11, 141);
      color: white;
    }

    tr:hover {
      background-color: #f1f1f1;
      cursor: pointer;
    }

    .form-container {
      background-color: white;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    label {
      display: block;
      margin-bottom: 10px;
      font-weight: 500;
      color:rgb(130, 15, 172);
    }

    input,
    textarea {
      width: 100%;
      padding: 10px;
      margin-bottom: 15px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 16px;
    }

    textarea {
      height: 100px;
    }

    button {
      padding: 12px;
      background-color: #6610f2;
      border: none;
      color: white;
      font-size: 18px;
      border-radius: 8px;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background-color: #0056b3;
    }

    .message {
      margin-top: 15px;
      padding: 10px;
      background-color: #dff0d8;
      border: 1px solid #d6e9c6;
      color: #3c763d;
      border-radius: 6px;
      display:
        <?php echo isset($message) ? 'block' : 'none'; ?>
      ;
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
    <h1>Manage Users</h1>

    <?php if (isset($message))
      echo "<div class='message'>$message</div>"; ?>

    <table>
      <thead>
        <tr>
          <th>User ID</th>
          <th>Name</th>
          <th>Username</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Address</th>
          
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $user): ?>
          <tr onclick="populateForm(<?php echo htmlspecialchars(json_encode($user)); ?>)">
            <td><?php echo $user['user_id']; ?></td>
            <td><?php echo $user['name']; ?></td>
            <td><?php echo $user['username']; ?></td>
            <td><?php echo $user['email']; ?></td>
            <td><?php echo $user['phone']; ?></td>
            <td><?php echo $user['address']; ?></td>
            
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="form-container">
      <h2>User Details</h2>
      <form method="POST">
        <input type="hidden" id="user_id" name="user_id">
        <label>Name</label>
        <input type="text" id="name" name="name">
        <label>Username</label>
        <input type="text" id="username" name="username">
        <label>Email</label>
        <input type="email" id="email" name="email">
        <label>Phone</label>
        <input type="text" id="phone" name="phone">
        <label>Address</label>
        <textarea id="address" name="address"></textarea>
        <button type="submit" name="update">Update</button>
        <button type="submit" name="delete" style="background-color: red;">Delete</button>
      </form>
    </div>
  </div>

  <script>
    function populateForm(user) {
      document.getElementById('user_id').value = user.user_id;
      document.getElementById('name').value = user.name;
      document.getElementById('username').value = user.username;
      document.getElementById('email').value = user.email;
      document.getElementById('phone').value = user.phone;
      document.getElementById('address').value = user.address;
    }
  </script>
</body>
<a href="adminout.php" class="backlink">← Back to Admin</a>

</html>