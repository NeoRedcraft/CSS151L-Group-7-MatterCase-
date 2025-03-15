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
    <meta charset="UTF-8">
    <title>View Matters</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>View Matters</h1>

    <?php if ($usertype == 0 || $usertype == 1): ?>
        <p><a href="add_matter_page.php">Add New Matter</a></p>
    <?php endif; ?>

    <p>
        <a href="<?php
            // Redirect to the appropriate dashboard based on usertype
            switch ($usertype) {
                case 0: // Admin
                    echo 'dashboard_admin.php';
                    break;
                case 1: // Partner
                    echo 'dashboard_partner.php';
                    break;
                case 2: // Lawyer
                    echo 'dashboard_lawyer.php';
                    break;
                case 3: // Paralegal
                    echo 'dashboard_paralegal.php';
                    break;
                case 4: // Messenger
                    echo 'dashboard_messenger.php';
                    break;
                default:
                    echo 'login_page.php'; // Fallback to login page
                    break;
            }
        ?>">Back to Dashboard</a>
    </p>

    <!-- Display Existing Matters -->
    <h2>Existing Matters</h2>
    <table>
        <thead>
            <tr>
                <?php if (!empty($data)): ?>
                    <?php foreach ($data[0] as $key => $value): ?>
                        <th><?php echo htmlspecialchars($key); ?></th>
                    <?php endforeach; ?>
                <?php endif; ?>
                <th>Edit Matter</th>
                <th>View Cases</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $row): ?>
                <tr>
                    <?php foreach ($row as $key => $value): ?>
                        <td>
                            <?php 
                            // Display decrypted title and description
                            echo htmlspecialchars($value);
                            ?>
                        </td>
                    <?php endforeach; ?>
                    <td>
                        <a href="edit_matter_page.php?matter_id=<?php echo $row['matter_id']; ?>">Edit</a>
                    </td>
                    <td>
                        <a href="view_cases_page.php?matter_id=<?php echo $row['matter_id']; ?>">View Cases</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>