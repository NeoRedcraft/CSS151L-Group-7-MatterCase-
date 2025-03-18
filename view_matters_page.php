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
$data = $result->fetch_all(MYSQLI_ASSOC);

// Decrypt the title and description fields
foreach ($data as &$row) {
    $row['title'] = decryptData($row['title'], $key, $method);
    $row['description'] = decryptData($row['description'], $key, $method);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
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

    <div class="container">
        <div>
            <a href="add_matter_page.php" class="btn add-btn">Add New Matter</a>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Edit Matter</th>
                        <th>View Cases</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['title']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><a href="edit_matter_page.php?matter_id=<?php echo $row['matter_id']; ?>" class="edit-btn">Edit</a></td>
                        <td><a href="view_cases_page.php?matter_id=<?php echo $row['matter_id']; ?>" class="edit-btn">View Cases</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
