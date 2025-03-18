<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php"); // Include encryption function
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/audit_log.php"); 

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

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $case_id = $_POST['case_id'];
    $evidence_type = $_POST['evidence_type'];
    $description = $_POST['description'];
    $submission_status = $_POST['submission_status'];

    // Handle file upload
    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = basename($_FILES['file']['name']);
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
        $max_size = 5 * 1024 * 1024; // 5 MB

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_name = uniqid() . "_" . $file_name;
            $file_path = $upload_dir . $file_name;
            $relative_path = "uploads/" . $file_name;

            if (move_uploaded_file($file_tmp, $file_path)) {
                $stmt = $conn->prepare("INSERT INTO evidence (case_id, evidence_type, file_path, description, submission_status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $case_id, $evidence_type, $relative_path, $description, $submission_status);

                if ($stmt->execute()) {
                    $action = "Added new $evidence_type evidence to case ID: $case_id, submission status: $submission_status";
                    logAction($conn, $user_id, $action, $key, $method);
                    header("Location: view_case_details.php?case_id=$case_id&success=1");
                    exit();
                } else {
                    header("Location: add_evidence_page.php?case_id=$case_id&error=1");
                    exit();
                }
                $stmt->close();
            } else {
                header("Location: add_evidence_page.php?case_id=$case_id&error=1");
                exit();
            }
        } else {
            header("Location: add_evidence_page.php?case_id=$case_id&error=1");
            exit();
        }
    } else {
        header("Location: add_evidence_page.php?case_id=$case_id&error=1");
        exit();
    }

    $conn->close();
}

$case_id = $_GET['case_id'] ?? ''; // Get the case ID from the URL
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Evidence</title>
    <link rel="stylesheet" href="add_invoice_page.css">
</head>
<body>
    <img src="img/logo.png" class="logo" alt="Logo">

    <div class="invoice-container">
        <h1>Add Evidence</h1>

        <!-- Display success or error messages -->
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green;">Evidence added successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red;">Failed to add evidence. Please try again.</p>
        <?php endif; ?>

        <!-- Form to Add New Evidence -->
        <form action="add_evidence_page.php" method="POST" enctype="multipart/form-data" class="invoice-form">
            <input type="hidden" name="case_id" value="<?php echo htmlspecialchars($case_id); ?>">

            <label for="evidence_type">Evidence Type:</label>
            <input type="text" id="evidence_type" name="evidence_type" required>

            <label for="file">File:</label>
            <input type="file" id="file" name="file" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required class="input-field"></textarea>

            <label for="submission_status">Submission Status:</label>
            <select id="submission_status" name="submission_status" required>
                <option value="Submitted">Submitted</option>
                <option value="Pending">Pending</option>
                <option value="Rejected">Rejected</option>
            </select>

            <button type="submit">Add Evidence</button>
        </form>

        <p><a href="view_case_details.php?case_id=<?php echo htmlspecialchars($case_id); ?>" class="button">Back to Case Details</a></p>
    </div>
</body>
</html>
