<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php");

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins and Partners only
if ($usertype != 0 && $usertype != 1) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $case_title = $_POST['case_title'];
    $court = $_POST['court'];
    $case_type = $_POST['case_type'];
    $status = $_POST['status'];
    $client_id = $_POST['client_id'];
    $matter_id = $_POST['matter_id'];

    $encryptedCaseTitle = encryptData($case_title, $key, $method);
    $encryptedCourt = encryptData($court, $key, $method);

    $stmt = $conn->prepare("INSERT INTO cases (case_title, court, case_type, status, client_id, matter_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $encryptedCaseTitle, $encryptedCourt, $case_type, $status, $client_id, $matter_id);

    if ($stmt->execute()) {
        header('Location: view_cases_page.php?success=1');
        exit();
    } else {
        header('Location: add_case_page.php?error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}

$clients = $conn->query("SELECT client_id, client_name FROM clients");
$matters = $conn->query("SELECT matter_id, title FROM matters");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case</title>
    <link rel="stylesheet" href="add_case_page.css">
</head>
<body>
    <img src="img/logo.png" alt="Logo" class="logo">
    <div class="container">
        <div class="title">Add New Case</div>

        <?php if (isset($_GET['success'])): ?>
            <p style="color: green; text-align: center;">Case added successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red; text-align: center;">Failed to add case. Please try again.</p>
        <?php endif; ?>

        <form action="add_case_page.php" method="POST">
            <div class="user-details">
                <div class="input-box">
                    <span class="details">Case Title:</span>
                    <input type="text" name="case_title" required>
                </div>
                <div class="input-box">
                    <span class="details">Court:</span>
                    <input type="text" name="court" required>
                </div>
                <div class="input-box">
                    <span class="details">Case Type:</span>
                    <input type="text" name="case_type" required>
                </div>
                <div class="input-box">
                    <span class="details">Status:</span>
                    <select name="status" required>
                        <option value="Active">Active</option>
                        <option value="Dismissed">Dismissed</option>
                        <option value="Closed">Closed</option>
                    </select>
                </div>
                <div class="input-box">
                    <span class="details">Client:</span>
                    <select name="client_id" required>
                        <?php while ($client = $clients->fetch_assoc()): ?>
                            <option value="<?php echo $client['client_id']; ?>">
                                <?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="input-box">
                    <span class="details">Matter:</span>
                    <select name="matter_id" required>
                        <?php while ($matter = $matters->fetch_assoc()): ?>
                            <option value="<?php echo $matter['matter_id']; ?>">
                                <?php echo htmlspecialchars(decryptData($matter['title'], $key, $method)); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            <div class="button">
                <input type="submit" value="Add Case">
            </div>
        </form>
        <div class="button">
            <a href="view_cases_page.php">
                <input type="button" value="Back to View Cases">
            </a>
        </div>
    </div>
</body>
</html>