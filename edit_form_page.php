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

// Ensure the uploads directory exists
$upload_dir = $_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Fetch the form to edit
$form_id = $_GET['form_id'];
$query = "SELECT * FROM forms WHERE form_id = $form_id";
$result = $conn->query($query);
$form = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $form_title = $_POST['form_title'];
    $submission_status = $_POST['submission_status'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = uniqid() . "_" . basename($_FILES['file']['name']);
        $file_path = $upload_dir . $file_name;
        $relative_path = "uploads/" . $file_name;

        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            if (!empty($form['file_path']) && file_exists($upload_dir . basename($form['file_path']))) {
                unlink($upload_dir . basename($form['file_path']));
            }
            $stmt = $conn->prepare("UPDATE forms SET form_title = ?, file_path = ?, submission_status = ? WHERE form_id = ?");
            $stmt->bind_param("sssi", $form_title, $relative_path, $submission_status, $form_id);
        } else {
            header("Location: edit_form_page.php?form_id=$form_id&error=1");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE forms SET form_title = ?, submission_status = ? WHERE form_id = ?");
        $stmt->bind_param("ssi", $form_title, $submission_status, $form_id);
    }

    if ($stmt->execute()) {
        header("Location: view_case_details.php?case_id={$form['case_id']}&success=1");
        exit();
    } else {
        header("Location: edit_form_page.php?form_id=$form_id&error=1");
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
    <title>Edit Form</title>
    <link rel="stylesheet" href="edit_form.css">
</head>
<body>
    <a href="view_case_details.php" class="back-link">Back to Case Details</a>
    <div class="container">
        <h2>Edit Form</h2>
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Form updated successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red;">Failed to update form. Please try again.</p>
        <?php endif; ?>
        <form action="edit_form_page.php?form_id=<?php echo $form_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="input-box">
                <label for="form_title">Form Title:</label>
                <input type="text" id="form_title" name="form_title" value="<?php echo htmlspecialchars($form['form_title']); ?>" required>
            </div>
            <div class="input-box">
                <label for="file">File:</label>
                <input type="file" id="file" name="file">
                <p>Current File: 
                    <?php if (!empty($form['file_path'])): ?>
                        <a href="/ITS122L-MatterCase/<?php echo htmlspecialchars($form['file_path']); ?>" target="_blank">View File</a>
                    <?php else: ?>
                        No file uploaded
                    <?php endif; ?>
                </p>
            </div>
            <div class="input-box">
                <label for="submission_status">Submission Status:</label>
                <select id="submission_status" name="submission_status" required>
                    <option value="Submitted" <?php echo $form['submission_status'] == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                    <option value="Pending" <?php echo $form['submission_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Rejected" <?php echo $form['submission_status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
            </div>
            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>
