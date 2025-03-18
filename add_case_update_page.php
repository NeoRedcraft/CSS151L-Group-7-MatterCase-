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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $case_id = $_POST['case_id'];
    $update_text = $_POST['update_text'];
    $updated_by = $user_id; // Use the logged-in user's ID

    // Validate that the user ID exists in the `users` table
    $user_check_query = "SELECT id FROM users WHERE id = ?";
    $stmt = $conn->prepare($user_check_query);
    $stmt->bind_param("i", $updated_by);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        die("Error: Invalid user ID. Please log in again.");
    }

    // Insert the new case update into the database
    $stmt = $conn->prepare("INSERT INTO case_updates (case_id, update_text, updated_by) VALUES (?, ?, ?)");
    $stmt->bind_param("isi", $case_id, $update_text, $updated_by);

    if ($stmt->execute()) {
        header("Location: view_case_details.php?case_id=$case_id&success=1");
        exit();
    } else {
        header("Location: add_case_update_page.php?case_id=$case_id&error=1");
        exit();
    }

    $stmt->close();
    $conn->close();
}

$case_id = $_GET['case_id']; // Get the case ID from the URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Case Update</title>
    <link rel="stylesheet" href="add_invoice_page.css"> <!-- Match previous styling -->
</head>
<body>
    <img src="img/logo.png" class="logo" alt="Logo"> <!-- Logo for consistency -->

    <div class="invoice-container"> <!-- Styled container -->
        <h1>Add Case Update</h1>

        <!-- Display success or error messages -->
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Case update added successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red;">Failed to add case update. Please try again.</p>
        <?php endif; ?>

        <!-- Form to Add a New Case Update -->
        <form action="add_case_update_page.php" method="POST" class="invoice-form">
            <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($case_id); ?>">

            <label for="update_text">Update Text:</label>
            <textarea id="update_text" name="update_text" required class="input-field"></textarea>

            <button type="submit">Add Update</button>
        </form>

        <a href="view_case_details.php?case_id=<?php echo htmlspecialchars($case_id); ?>" class="button">Back to Case Details</a>
    </div>
</body>
</html>
