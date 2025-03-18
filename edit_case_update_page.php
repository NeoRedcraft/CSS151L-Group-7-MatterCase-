<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php"); // Include encryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Admins, Partners, and Lawyers only
if ($usertype != 0 && $usertype != 1 && $usertype != 2) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch the case update to edit
$update_id = $_GET['update_id'];
$query = "SELECT * FROM case_updates WHERE update_id = $update_id";
$result = $conn->query($query);
$update = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $update_text = $_POST['update_text'];

    // Update the case update in the database
    $stmt = $conn->prepare("UPDATE case_updates SET update_text = ? WHERE update_id = ?");
    $stmt->bind_param("si", $update_text, $update_id);

    if ($stmt->execute()) {
        // Redirect back to the case details page with a success message
        header("Location: view_case_details.php?case_id={$update['case_id']}&success=1");
        exit();
    } else {
        // Redirect back to the edit case update page with an error message
        header("Location: edit_case_update_page.php?update_id=$update_id&error=1");
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
    <title>Edit Case Update</title>
    <link rel="stylesheet" href="edit_case_update_page.css"> <!-- Link to external CSS file -->
</head>
<body>
    <a href="dashboard.php" class="back-link">Back to Dashboard</a>
    <img src="img/logo.png" class="logo" alt="">

    <div class="container">
        <h2>Edit Case Update</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_GET['success'])): ?>
            <p id="successMessage" style="color: green;">Case update updated successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p id="errorMessage" style="color: red;">Failed to update case update. Please try again.</p>
        <?php endif; ?>

        <!-- Form to Edit a Case Update -->
        <form action="edit_case_update_page.php?update_id=<?php echo $update_id; ?>" method="POST">
            <div class="input-box">
                <label for="update_text">Update Text:</label>
                <textarea id="update_text" name="update_text" required><?php echo htmlspecialchars($update['update_text']); ?></textarea>
            </div>

            <button type="submit">Update</button>
        </form>

        <!-- Back to Case Details Button -->
        <a href="view_case_details.php?case_id=<?php echo $update['case_id']; ?>" class="back-button">Back to Case Details</a>
    </div>
</body>
</html>
