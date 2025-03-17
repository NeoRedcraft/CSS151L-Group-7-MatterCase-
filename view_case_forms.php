<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forms</title>
</head>
<body>
    <h1>Forms</h1>
    <a class="back-link" href="view_case_details.php?case_id=<?php echo $case_id; ?>">Back to Dashboard</a>

    <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
        <p><a href="add_form_page.php?case_id=<?php echo $case_id; ?>">Add New Form</a></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Form ID</th>
                <th>Form Title</th>
                <th>Submission Status</th>
                <th>Uploaded At</th>
                <th>File</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($forms as $form): ?>
                <tr>
                    <td><?php echo htmlspecialchars($form['form_id']); ?></td>
                    <td><?php echo htmlspecialchars($form['form_title']); ?></td>
                    <td><?php echo htmlspecialchars($form['submission_status']); ?></td>
                    <td><?php echo htmlspecialchars($form['uploaded_at']); ?></td>
                    <td>
                        <?php if (!empty($form['file_path'])): ?>
                            <a href="view_file.php?file=<?php echo urlencode($form['file_path']); ?>" target="_blank">View File</a>
                        <?php else: ?>
                            No file uploaded
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($usertype == 0 || $usertype == 1 || $usertype == 2): ?>
                            <a href="edit_form_page.php?form_id=<?php echo $form['form_id']; ?>">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>