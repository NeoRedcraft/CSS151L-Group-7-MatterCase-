<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Updates</title>
</head>
<body>
    <h1>Case Updates</h1>
    <a class="back-link" href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Dashboard</a>

    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <p><a href="add_case_update_page.php?case_id=<?php echo $case_id; ?>">Add New Update</a></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Update ID</th>
                <th>Update Text</th>
                <th>Updated By</th>
                <th>Updated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($case_updates as $update): ?>
                <tr>
                    <td><?php echo htmlspecialchars($update['update_id']); ?></td>
                    <td><?php echo htmlspecialchars($update['update_text']); ?></td>
                    <td><?php echo htmlspecialchars($update['updated_by']); ?></td>
                    <td><?php echo htmlspecialchars($update['updated_at']); ?></td>
                    <td>
                        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                            <a href="edit_case_update_page.php?update_id=<?php echo $update['update_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>