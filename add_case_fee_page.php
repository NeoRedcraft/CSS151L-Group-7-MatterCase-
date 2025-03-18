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
    $case_id = $_POST['case_id'];
    $amount = $_POST['amount'];
    $fee_description = $_POST['fee_description'];
    $payment_status = $_POST['payment_status'];
    $due_date = $_POST['due_date'];

    // Insert the new case fee into the database
    $stmt = $conn->prepare("INSERT INTO case_fees (case_id, amount, fee_description, payment_status, due_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idsss", $case_id, $amount, $fee_description, $payment_status, $due_date);

    if ($stmt->execute()) {
        header("Location: view_case_details.php?case_id=$case_id&success=1");
        exit();
    } else {
        header("Location: add_case_fee_page.php?case_id=$case_id&error=1");
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Case Fee</title>
    <link rel="stylesheet" href="add_case_fee_page.css">
</head>
<body>
    <img src="img/logo.png" alt="Logo" class="logo">
    
    <div class="container">
        <h1 class="title">Add Case Fee</h1>

        <!-- Display success or error messages -->
        <?php if (isset($_GET['success'])): ?>
            <p style="color: green; text-align: center;">Case fee added successfully!</p>
        <?php elseif (isset($_GET['error'])): ?>
            <p style="color: red; text-align: center;">Failed to add case fee. Please try again.</p>
        <?php endif; ?>

        <!-- Form to Add a New Case Fee -->
        <form action="add_case_fee_page.php" method="POST">
            <input type="hidden" name="case_id" value="<?php echo $case_id; ?>">

            <div class="user-details">
                <div class="input-box">
                    <label class="details" for="amount">Amount:</label>
                    <input type="number" id="amount" name="amount" step="0.01" required>
                </div>

                <div class="input-box">
                    <label class="details" for="due_date">Due Date:</label>
                    <input type="date" id="due_date" name="due_date" required>
                </div>
            </div>

            <div class="input-box" style="width: 100%;">
                <label class="details" for="fee_description">Description:</label>
                <textarea id="fee_description" name="fee_description" required style="width: 100%; height: 80px; border-radius: 5px; padding: 10px; border: 1px solid #ccc;"></textarea>
            </div>

            <div class="input-box" style="width: 100%;">
                <label class="details" for="payment_status">Payment Status:</label>
                <select id="payment_status" name="payment_status" required>
                    <option value="Unpaid">Unpaid</option>
                    <option value="Paid">Paid</option>
                    <option value="Overdue">Overdue</option>
                </select>
            </div>

            <div class="button">
                <input type="submit" value="Add Fee">
            </div>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            <a href="view_case_details.php?case_id=<?php echo $case_id; ?>" style="text-decoration: none; color: #113dff; font-weight: 600;">Back to Case Details</a>
        </p>
    </div>
</body>
</html>
