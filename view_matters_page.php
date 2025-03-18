<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php"); // Include decryption function

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    header('Location: login_page.php');
    exit();
}

$user_id = $_SESSION['id'];
$usertype = $_SESSION['usertype'];

// Restrict access to Paralegals and Messengers
if ($usertype == 3 || $usertype == 4) {
    header('Location: view_cases_page.php');
    exit();
}

// Connect to the database
$conn = new mysqli('localhost', 'root', '', 'mattercase');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch matters based on user role
if ($usertype == 0 || $usertype == 1) {
    // Admin and Partner can see all matters
    $query = "SELECT * FROM matters";
} elseif ($usertype == 2) {
    // Lawyers can only see matters assigned to them
    $query = "
        SELECT DISTINCT m.* 
        FROM matters m
        JOIN cases c ON m.matter_id = c.matter_id
        JOIN case_lawyers cl ON c.case_id = cl.case_id
        WHERE cl.lawyer_id = $user_id
    ";
}

$result = $conn->query($query);
if (!$result) {
    die("Query failed: " . $conn->error);
}

$matters = $result->fetch_all(MYSQLI_ASSOC);

// Decrypt the title and description fields
foreach ($matters as &$matter) {
    $matter['title'] = decryptData($matter['title'], $key, $method);
    $matter['description'] = decryptData($matter['description'], $key, $method);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Matters Dashboard</title>
    <link rel="stylesheet" href="view_matters_page.css">
</head>
<body>
    <div class="container">
        <span class="logo">Client <span>Matters</span></span>
        <div>
            <a href="logout.php" class="btn back-btn">Logout</a>
            <a href="dashboard.php" class="btn back-btn">Dashboard</a>
        </div>
    </div>

        <!-- Main Content -->
        <div class="flex-grow flex justify-center mt-2">
            <div class="bg-gradient-to-b from-gray-700 to-gray-900 text-center rounded-lg p-8 shadow-lg w-[90%]">
                <?php if ($usertype == 0 || $usertype == 1): ?>
                    <a href="add_matter_page.php">
                        <button class="bg-yellow-300 text-gray-900 font-semibold py-3 rounded-lg shadow-md w-full h-12">Add New Matter</button>
                    </a>
                <?php endif; ?>

                <table width="90%">
                    <thead>
                        <tr>
                            <th>Matter ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($matters as $matter): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($matter['matter_id']); ?></td>
                                <td><?php echo htmlspecialchars($matter['title']); ?></td>
                                <td><?php echo htmlspecialchars($matter['description']); ?></td>
                                <td><?php echo htmlspecialchars($matter['status']); ?></td>
                                <td><?php echo htmlspecialchars($matter['created_at']); ?></td>
                                <td>
                                    <a href="edit_matter_page.php?matter_id=<?php echo $matter['matter_id']; ?>" class="edit-btn">Edit</a>
                                    | <a href="view_cases_page.php?matter_id=<?php echo $matter['matter_id']; ?>" class="edit-btn">View Cases</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
