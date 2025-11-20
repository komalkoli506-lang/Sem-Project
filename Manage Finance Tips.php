<?php
session_start();
$host = 'localhost';
$dbname = 'CPP';
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password

// Create a database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check if the connection was successful
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
$tip = ['id' => '', 'title' => '', 'description' => ''];

// Handling Edit Mode - Fetch Data if Edit Button is Clicked
if (isset($_GET['edit_id'])) {
    $editId = intval($_GET['edit_id']);
    $editQuery = "SELECT * FROM finance_tips WHERE id = ?";
    $stmt = $conn->prepare($editQuery);
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $tip = $result->fetch_assoc();
    }
    $stmt->close();
}

// Insert or update a finance tip
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['tipTitle']);
    $description = trim($_POST['tipDescription']);
    $tipId = isset($_POST['tipId']) ? intval($_POST['tipId']) : null;

    if ($tipId) {
        // Update the tip
        $updateQuery = "UPDATE finance_tips SET title = ?, description = ? WHERE id = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param("ssi", $title, $description, $tipId);
    } else {
        // Insert new tip
        $insertQuery = "INSERT INTO finance_tips (title, description) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("ss", $title, $description);
    }

    $stmt->execute();
    $stmt->close();
    header("Location: Manage Finance Tips.php"); // Redirect after submission
    exit();
}

// Delete a finance tip
if (isset($_GET['delete_id'])) {
    $deleteId = intval($_GET['delete_id']);
    $deleteQuery = "DELETE FROM finance_tips WHERE id = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();
    header("Location: Manage Finance Tips.php"); // Redirect after deletion
    exit();
}

// Fetch all finance tips from the database
$tipsQuery = "SELECT * FROM finance_tips ORDER BY created_at DESC";
$tipsResult = $conn->query($tipsQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Personal Finance Tips</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        h1 {
            font-size: 36px;
            color:rgb(126, 12, 158);
            margin-bottom: 20px;
            text-align: center;
        }

        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .form-section h2 {
            font-size: 24px;
            color:rgb(119, 14, 154);
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-size: 16px;
            margin-bottom: 5px;
            display: block;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-group button {
            padding: 10px 20px;
            background: linear-gradient(45deg,rgb(135, 8, 150),rgb(121, 9, 132));
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-group button:hover {
            background-color:rgb(122, 14, 141);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        th,
        td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color:rgb(109, 5, 128);
            color: white;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .actions button {
            padding: 6px 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            background-color:rgb(141, 11, 155);
            color: white;
            transition: background-color 0.3s ease;
        }

        .actions button:hover {
            background-color:rgb(128, 11, 149);
        }

        .actions .delete-btn {
            background-color: #dc3545;
        }

        .actions .delete-btn:hover {
            background-color: #c82333;
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
        <h1>Manage Personal Finance Tips</h1>

        <!-- Form to add or edit finance tips -->
        <div class="form-section">
            <h2><?= $tip['id'] ? 'Edit Personal Finance Tip' : 'Add New Personal Finance Tip' ?></h2>
            <form method="POST" action="Manage Finance Tips.php">
                <div class="form-group">
                    <label for="tipTitle">Tip Title</label>
                    <input type="text" id="tipTitle" name="tipTitle" placeholder="Enter tip title"
                        value="<?= htmlspecialchars($tip['title']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="tipDescription">Tip Description</label>
                    <textarea id="tipDescription" name="tipDescription" placeholder="Enter tip description"
                        required><?= htmlspecialchars($tip['description']) ?></textarea>
                </div>
                <input type="hidden" name="tipId" value="<?= $tip['id'] ?>">
                <div class="form-group">
                    <button type="submit"><?= $tip['id'] ? 'Update Tip' : 'Add Tip' ?></button>
                </div>
            </form>
        </div>

        <!-- Table displaying existing tips -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($tip = $tipsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?= $tip['id'] ?></td>
                        <td><?= htmlspecialchars($tip['title']) ?></td>
                        <td><?= htmlspecialchars($tip['description']) ?></td>
                        <td class='actions'>
                            <a href="Manage Finance Tips.php?edit_id=<?= $tip['id'] ?>"><button>Edit</button></a>
                            <a href="Manage Finance Tips.php?delete_id=<?= $tip['id'] ?>"
                                onclick="return confirm('Are you sure?')"><button class="delete-btn">Delete</button></a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
<a href="adminout.php" class="backlink">← Back to Admin</a>

</html>

<?php $conn->close(); ?>