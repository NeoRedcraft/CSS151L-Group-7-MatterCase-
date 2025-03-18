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

$evidence_id = $_GET['evidence_id'];
$query = "SELECT * FROM evidence WHERE evidence_id = $evidence_id";
$result = $conn->query($query);
$evidence = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $evidence_type = $_POST['evidence_type'];
    $description = $_POST['description'];
    $submission_status = $_POST['submission_status'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
        $file_name = basename($_FILES['file']['name']);
        $file_tmp = $_FILES['file']['tmp_name'];
        $file_size = $_FILES['file']['size'];
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
        $max_size = 5 * 1024 * 1024;

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $file_name = uniqid() . "_" . $file_name;
            $file_path = "uploads/" . $file_name;
            
            if (move_uploaded_file($file_tmp, $_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/" . $file_path)) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/" . $evidence['file_path'])) {
                    unlink($_SERVER['DOCUMENT_ROOT'] . "/ITS122L-MatterCase/" . $evidence['file_path']);
                }
                $stmt = $conn->prepare("UPDATE evidence SET evidence_type = ?, file_path = ?, description = ?, submission_status = ? WHERE evidence_id = ?");
                $stmt->bind_param("ssssi", $evidence_type, $file_path, $description, $submission_status, $evidence_id);
            } else {
                header("Location: edit_evidence_page.php?evidence_id=$evidence_id&error=1");
                exit();
            }
        } else {
            header("Location: edit_evidence_page.php?evidence_id=$evidence_id&error=1");
            exit();
        }
    } else {
        $stmt = $conn->prepare("UPDATE evidence SET evidence_type = ?, description = ?, submission_status = ? WHERE evidence_id = ?");
        $stmt->bind_param("sssi", $evidence_type, $description, $submission_status, $evidence_id);
    }

    if ($stmt->execute()) {
        header("Location: view_case_details.php?case_id={$evidence['case_id']}&success=1");
        exit();
    } else {
        header("Location: edit_evidence_page.php?evidence_id=$evidence_id&error=1");
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
    <title>Edit Evidence</title>
    <link rel="stylesheet" href="edit_evidence_page.css"> <!-- Link to external CSS file -->
</head>
<body>
    <div class="container"> <!-- Added container -->
        <h1>Edit Evidence</h1>

        <!-- Success or error messages -->
        <?php if (isset($_GET['success'])): ?>
            <p id="successMessage" style="color: green;">Evidence updated successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p id="errorMessage" style="color: red;">Failed to update evidence. Please try again.</p>
        <?php endif; ?>

        <form action="edit_evidence_page.php?evidence_id=<?php echo $evidence_id; ?>" method="POST" enctype="multipart/form-data">
            <label for="evidence_type">Evidence Type:</label>
            <input type="text" id="evidence_type" name="evidence_type" value="<?php echo htmlspecialchars($evidence['evidence_type']); ?>" required><br><br>

            <label for="file">File:</label>
            <input type="file" id="file" name="file"><br><br>
            <p>Current File: 
                <?php if (!empty($evidence['file_path'])): ?>
                    <a href="/ITS122L-MatterCase/view_file.php?file=<?php echo urlencode($evidence['file_path']); ?>" target="_blank"><?php echo basename($evidence['file_path']); ?></a>
                <?php else: ?>
                    No file uploaded
                <?php endif; ?>
            </p>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($evidence['description']); ?></textarea><br><br>

            <label for="submission_status">Submission Status:</label>
            <select id="submission_status" name="submission_status" required>
                <option value="Submitted" <?php echo $evidence['submission_status'] == 'Submitted' ? 'selected' : ''; ?>>Submitted</option>
                <option value="Pending" <?php echo $evidence['submission_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="Rejected" <?php echo $evidence['submission_status'] == 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select><br><br>

            <button type="submit">Update</button>
        </form>

        <button onclick="window.location.href='view_case_details.php?case_id=<?php echo $evidence['case_id']; ?>'">Back to Case Details</button>
    </div>
</body>
</html>
