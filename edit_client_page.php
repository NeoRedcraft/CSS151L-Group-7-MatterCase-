<?php
include_once($_SERVER['DOCUMENT_ROOT'] . "/MatterCase/Functions/edit_client.php"); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Client</title>
    <link rel="stylesheet" href="edit_client.css"> <!-- Link to external CSS file -->
    <script>
        function addMatters() {
            const selectedMatters = [];
            document.querySelectorAll('input[name="matter_ids[]"]:checked').forEach((checkbox) => {
                selectedMatters.push(checkbox.value);
            });

            if (selectedMatters.length === 0) {
                alert("Please select at least one matter.");
                return;
            }

            selectedMatters.forEach((matterId) => {
                const matterElement = document.createElement('li');
                matterElement.classList.add('matter-item');
                matterElement.setAttribute('data-matter-id', matterId);

                const matterTitle = document.getElementById(`matter-title-${matterId}`).textContent;

                matterElement.innerHTML = `
                    ${matterTitle}
                    <button type="button" onclick="removeMatter(this)">Remove</button>
                `;
                document.getElementById('selected-matters-list').appendChild(matterElement);
            });
        }

        function removeMatter(button) {
            button.parentElement.remove();
        }

        function filterMatters() {
            const searchText = document.getElementById('search').value.toLowerCase();
            const matters = document.querySelectorAll('.matter-list div');

            matters.forEach((matter) => {
                const matterText = matter.textContent.toLowerCase();
                matter.style.display = matterText.includes(searchText) ? 'block' : 'none';
            });
        }
    </script>
</head>
<body>
    <a href="<?php
        switch ($usertype) {
            case 0: echo 'dashboard_admin.php'; break;
            case 1: echo 'dashboard_partner.php'; break;
            default: echo 'login_page.php'; break;
        }
    ?>" class="back-link">Back to Dashboard</a>

    <div class="container">
        <h1>Edit Client</h1>

        <form action="edit_client_page.php?client_id=<?php echo $client_id; ?>" method="POST" enctype="multipart/form-data">
            <div class="input-box">
                <label for="client-name">Client Name:</label>
                <input type="text" id="client-name" name="client_name" value="<?php echo htmlspecialchars($client_name); ?>" required>
            </div>
            <div class="input-box">
                <label for="client-email">Email:</label>
                <input type="email" id="client-email" name="client_email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="file-input-container">
                <label class="file-label" for="client-file">Upload File:</label>
                <input type="file" id="client-file" name="client_file">
            </div>
            <button type="submit" name="update">Update Client</button>
        </form>

        <div class="related-matters">
            <h2>Related Matters</h2>
            <form method="post" action="edit_client_page.php?client_id=<?php echo $client_id; ?>">
                <ul id="related-matters-list">
                    <?php
                    $query = "SELECT m.matter_id, m.title FROM matters m
                              JOIN client_matters cm ON m.matter_id = cm.matter_id
                              WHERE cm.client_id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $client_id);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $decrypted_title = decryptData($row['title'], $key, $method);
                            echo "<li class='matter-item' data-matter-id='" . htmlspecialchars($row['matter_id']) . "'>
                                <input type='checkbox' name='remove_matter_ids[]' value='" . htmlspecialchars($row['matter_id']) . "'> 
                                <span id='matter-title-" . htmlspecialchars($row['matter_id']) . "'>" . htmlspecialchars($decrypted_title) . "</span>
                                <button type='button' onclick='removeMatter(this)'>Remove</button>
                            </li>";
                        }
                    } else {
                        echo "<p>No related matters found.</p>";
                    }
                    ?>
                </ul>
            </form>
        </div>

        <h2>All Matters</h2>
        <input type="text" id="search" placeholder="Search matters..." onkeyup="filterMatters()">
        <div class="matter-list">
            <?php
            $query = "SELECT matter_id, title FROM matters";
            $result = $conn->query($query);

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $decrypted_title = decryptData($row['title'], $key, $method);
                    echo "<div>
                        <input type='checkbox' name='matter_ids[]' value='" . htmlspecialchars($row['matter_id']) . "' id='matter-id-" . htmlspecialchars($row['matter_id']) . "'>
                        <span id='matter-title-" . htmlspecialchars($row['matter_id']) . "'>" . htmlspecialchars($decrypted_title) . "</span>
                    </div>";
                }
            } else {
                echo "<p>No matters found.</p>";
            }
            ?>
        </div>
        <button onclick="addMatters()">Add Selected Matters</button>
    </div>
</body>
</html>
