<?php
// Include the logic file
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/view_case_details.php"); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Case Details Dashboard</title>
</head>
<body>
    <h1>Case Details Dashboard</h1>
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
    ?>">Back to Main Dashboard</a>

    <h2>Case: <?php echo htmlspecialchars($case['case_title']); ?></h2>
    <p><strong>Court:</strong> <?php echo htmlspecialchars($case['court']); ?></p>
    <p><strong>Case Type:</strong> <?php echo htmlspecialchars($case['case_type']); ?></p>
    <p><strong>Status:</strong> <?php echo htmlspecialchars($case['status']); ?></p>
    <p><strong>Created At:</strong> <?php echo htmlspecialchars($case['created_at']); ?></p>

    <!-- Links to individual sections -->
    <a class="section-link" href="view_case_updates.php?case_id=<?php echo $case_id; ?>">Case Updates</a>
    <a class="section-link" href="view_case_evidence.php?case_id=<?php echo $case_id; ?>">Evidence</a>
    <a class="section-link" href="view_case_forms.php?case_id=<?php echo $case_id; ?>">Forms</a>
    <a class="section-link" href="view_case_fees.php?case_id=<?php echo $case_id; ?>">Case Fees</a>
    <a class="section-link" href="view_case_invoices.php?case_id=<?php echo $case_id; ?>">Invoices</a>
</body>
</html>