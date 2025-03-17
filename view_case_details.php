<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details Dashboard</title>
    <link rel="stylesheet" href="view_case_details.css"> <!-- Link to the updated CSS -->
</head>
<body>
    <div class="dashboard-container">
        <h1>Case Details Dashboard</h1>
        
        <!-- Back to Main Dashboard Link -->
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
        ?>" class="logout-btn">Back to Main Dashboard</a>
        
        <!-- Logo -->
        <img src="img/logo.png" alt="Logo" class="logo">
        
        <!-- Case Information -->
        <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>
        <p><strong>Court:</strong> <?php echo htmlspecialchars($case['court']); ?></p>
        <p><strong>Case Type:</strong> <?php echo htmlspecialchars($case['case_type']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($case['status']); ?></p>
        <p><strong>Created At:</strong> <?php echo htmlspecialchars($case['created_at']); ?></p>

        <!-- Links to individual sections (now buttons) -->
        <div class="buttons-container">
            <a class="action-btn" href="view_case_updates.php?case_id=<?php echo $case_id; ?>">Case Updates</a>
            <a class="action-btn" href="view_case_evidence.php?case_id=<?php echo $case_id; ?>">Evidence</a>
            <a class="action-btn" href="view_case_forms.php?case_id=<?php echo $case_id; ?>">Forms</a>
            <a class="action-btn" href="view_case_fees.php?case_id=<?php echo $case_id; ?>">Case Fees</a>
            <a class="action-btn" href="view_case_invoices.php?case_id=<?php echo $case_id; ?>">Invoices</a>
        </div>
    </div>
</body>
</html>
