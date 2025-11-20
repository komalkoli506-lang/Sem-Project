<?php
session_start();
$conn = new mysqli("localhost", "root", "", "CPP");

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
$user_id = $_SESSION['user_id'] ?? 1; // Replace with dynamic session value

// Handle Profile Update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $username = $_POST["username"];
    $phone = $_POST["phone"];
    $address = $_POST["address"];
   

   
    

    $sql = "UPDATE users SET name=?, username=?, phone=?, address=?, WHERE user_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $name, $username, $phone, $address, $user_id);
    echo ($stmt->execute()) ? "Profile updated!" : "Error updating profile.";
    exit;
}

// Fetch User Data
$sql = "SELECT * FROM users WHERE user_id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f0f8ff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
            margin: 0;
        }

        .container {
            width: 700px;
            /* Increased width */
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
        }

        h1 {
            text-align: center;
            color:rgb(143, 14, 152);
            margin-bottom: 20px;
        }

        .profile-photo {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-photo img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
        }

        .profile-photo button {
            margin-top: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            width: 35%;
            font-weight: bold;
            color: #333;
        }

        td {
            width: 65%;
        }

        input {
            width: 100%;
            padding: 8px;
            margin: 5px 0;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .progress-bar {
            width: 100%;
            background: #ddd;
            height: 12px;
            border-radius: 6px;
            margin: 15px 0;
        }

        .progress-bar-fill {
            width: 80%;
            height: 100%;
            background:rgb(122, 15, 155);
            border-radius: 6px;
        }

        button {
            background:rgb(118, 10, 145);
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: block;
            width: 100%;
            margin-top: 10px;
            font-size: 16px;
        }

        button:hover {
            background:rgb(104, 12, 141);
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
        <h1>User Profile</h1>

        <table>
            <tr>
                <th>Name:</th>
                <td><input type="text" id="name" value="<?= $user['name'] ?>" disabled></td>
            </tr>
            <tr>
                <th>Username:</th>
                <td><input type="text" id="username" value="<?= $user['username'] ?>" disabled></td>
            </tr>
            <tr>
                <th>Email:</th>
                <td><input type="email" value="<?= $user['email'] ?>" disabled></td>
            </tr>
            <tr>
                <th>Phone:</th>
                <td><input type="text" id="phone" value="<?= $user['phone'] ?>" disabled></td>
            </tr>
            <tr>
                <th>Address:</th>
                <td><input type="text" id="address" value="<?= $user['address'] ?>" disabled></td>
            </tr>
           
        </table>
        <div class="progress-bar">
            <div class="progress-bar-fill" id="progressBar">80%</div>
        </div>
        <button id="editButton">Edit Profile</button>
        <button id="saveButton" style="display:none;">Save Profile</button>
        <a href="logouthomepage.php" class="backlink">← Back to Homepage</a>

    </div>

    <script>
        document.getElementById("editButton").addEventListener("click", function () {
            document.querySelectorAll("input").forEach(input => input.disabled = false);
            document.getElementById("editButton").style.display = "none";
            document.getElementById("saveButton").style.display = "inline-block";
        });

        document.getElementById("saveButton").addEventListener("click", function () {
            let formData = new FormData();
            formData.append("name", document.getElementById("name").value);
            formData.append("username", document.getElementById("username").value);
            formData.append("phone", document.getElementById("phone").value);
            formData.append("address", document.getElementById("address").value);
           
            fetch("profile.php", {
                method: "POST",
                body: formData
            })
                .then(response => response.text())
                .then(data => {
                    alert(data);
                    location.reload();
                });
        });
        function updateProgressBar() {
            let inputs = document.querySelectorAll("input");
            let filledCount = 0;
            let totalCount = inputs.length;

            inputs.forEach(input => {
                if (input.value.trim() !== "") {
                    filledCount++;
                }
            });

            let progress = Math.round((filledCount / totalCount) * 100);
            let progressBar = document.getElementById("progressBar");

            progressBar.style.width = progress + "%";
            progressBar.textContent = progress + "%";
        }

        // Run on page load
        updateProgressBar();

        // Update progress bar when editing fields
        document.querySelectorAll("input").forEach(input => {
            input.addEventListener("input", updateProgressBar);
        });

    </script>
</body>

</html>