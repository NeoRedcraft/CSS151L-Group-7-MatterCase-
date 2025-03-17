<?php
session_start();
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/decrypt.php");
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/encryption.php");

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

$clientQuery = "SELECT * FROM clients";
$clientResult = $conn->query($clientQuery);

if (!$clientResult) {
    die("Client query failed: " . $conn->error);
}

$clients = $clientResult->fetch_all(MYSQLI_ASSOC);

$matterQuery = "
    SELECT cm.client_id, m.title 
    FROM client_matters cm
    LEFT JOIN matters m ON cm.matter_id = m.matter_id
";
$matterResult = $conn->query($matterQuery);

if (!$matterResult) {
    die("Matter query failed: " . $conn->error);
}

$matters = $matterResult->fetch_all(MYSQLI_ASSOC);
$mattersByClient = [];
foreach ($matters as $matter) {
    $clientId = $matter['client_id'];
    if (!isset($mattersByClient[$clientId])) {
        $mattersByClient[$clientId] = [];
    }
    $mattersByClient[$clientId][] = $matter['title'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Clients</title>
    <link rel="stylesheet" href="view_clients_page.css">
</head>
<body>
    <img src="img/logo.png" alt="Logo" class="logo">
    <div class="container">
        <h1>View Clients</h1>
        <p>
            <a href="<?php
                switch ($usertype) {
                    case 0: echo 'dashboard_admin.php'; break;
                    case 1: echo 'dashboard_partner.php'; break;
                    case 2: echo 'dashboard_lawyer.php'; break;
                    default: echo 'login_page.php';
                }
            ?>" class="btn back-btn">Back to Dashboard</a>
        </p>
        <?php if ($usertype == 0 || $usertype == 1): ?>
            <p><a href="add_client_page.php" class="btn add-btn">Add New Client</a></p>
        <?php endif; ?>
        <h2>Existing Clients</h2>
        <table>
            <thead>
                <tr>
                    <th>Client ID</th>
                    <th>Client Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Profile Picture</th>
                    <th>Created At</th>
                    <th>Matters</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clients as $client): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($client['client_id']); ?></td>
                        <td><?php echo htmlspecialchars(decryptData($client['client_name'], $key, $method)); ?></td>
                        <td><?php echo htmlspecialchars(decryptData($client['email'], $key, $method)); ?></td>
                        <td><?php echo htmlspecialchars(decryptData($client['address'], $key, $method)); ?></td>
                        <td><img src="<?php echo htmlspecialchars(decryptData($client['profile_picture'], $key, $method)); ?>" alt="Profile" width="50"></td>
                        <td><?php echo htmlspecialchars($client['created_at']); ?></td>
                        <td>
                            <?php
                            $clientId = $client['client_id'];
                            $clientMatters = $mattersByClient[$clientId] ?? [];
                            $decryptedMatters = array_map(fn($m) => decryptData($m, $key, $method), $clientMatters);
                            echo htmlspecialchars(implode(', ', $decryptedMatters) ?: 'No matters assigned');
                            ?>
                        </td>
                        <td>
                            <a href="view_cases_page.php?client_id=<?php echo $client['client_id']; ?>" class="edit-btn">View Details</a>
                            <?php if ($usertype == 0 || $usertype == 1): ?>
                                | <a href="edit_client_page.php?client_id=<?php echo $client['client_id']; ?>" class="edit-btn">Edit</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
