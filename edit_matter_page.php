<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/audit_log.php");

if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

if ($usertype == 3 || $usertype == 4) {
    header('Location: view_cases_page.php');
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['matter_id'])) {
    $matter_id = $_GET['matter_id'];
    $query = "SELECT * FROM matters WHERE matter_id = $matter_id";
    $result = $conn->query($query);
    $matter = $result->fetch_assoc();

    if (!$matter) {
        echo "Matter not found.";
        exit();
    }

    $title = decryptData($matter['title'], $key, $method);
    $description = decryptData($matter['description'], $key, $method);
    $status = $matter['status'];
} else {
    echo "Matter ID not provided.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_title = $_POST['title'];
    $new_description = $_POST['description'];
    $new_status = $_POST['status'];

    $changes = [];
    if ($new_title !== $title) {
        $changes[] = "Title: '$title' to '$new_title'";
    }
    if ($new_description !== $description) {
        $changes[] = "Description: '$description' to '$new_description'";
    }
    if ($new_status !== $status) {
        $changes[] = "Status: '$status' to '$new_status'";
    }

    if (!empty($changes)) {
        $action = "Updated matter ID $matter_id: " . implode(", ", $changes);
        logAction($conn, $user_id, $action, $key, $method);
    }

    $encrypted_title = encryptData($new_title, $key, $method);
    $encrypted_description = encryptData($new_description, $key, $method);

    $stmt = $conn->prepare("UPDATE matters SET title = ?, description = ?, status = ? WHERE matter_id = ?");
    $stmt->bind_param("sssi", $encrypted_title, $encrypted_description, $new_status, $matter_id);

    if ($stmt->execute()) {
        header('Location: view_matters_page.php?success=1');
        exit();
    } else {
        header('Location: edit_matter_page.php?matter_id=' . $matter_id . '&error=1');
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Matter</title>
    <link rel="stylesheet" href="edit_matter_page.css">
</head>
<body>

    <!-- Back to Dashboard Link -->
    <a href="view_matters_page.php" class="back-link">Back to View Matters</a>

    <img src="img/logo.png" class="logo" alt="Logo">

    <!-- Form Container -->
    <div class="container">
        <h2>Edit Matter</h2>

        <!-- Success and Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Matter updated successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red;">Failed to update matter. Please try again.</p>
        <?php endif; ?>

        <!-- Form to Edit Matter -->
        <form action="edit_matter_page.php?matter_id=<?php echo $matter_id; ?>" method="POST">
            <div class="input-box">
                <label for="title">Title:</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($title); ?>" required>
            </div>

            <div class="input-box">
                <label for="description">Description:</label>
                <textarea id="description" name="description" required><?php echo htmlspecialchars($description); ?></textarea>
            </div>

            <div class="input-box">
                <label for="status">Status:</label>
                <select id="status" name="status" required>
                    <option value="Open" <?php echo ($status === 'Open') ? 'selected' : ''; ?>>Open</option>
                    <option value="Closed" <?php echo ($status === 'Closed') ? 'selected' : ''; ?>>Closed</option>
                    <option value="Pending" <?php echo ($status === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                </select>
            </div>

            <button type="submit">Update Matter</button>
        </form>
    </div>

</body>
</html>
