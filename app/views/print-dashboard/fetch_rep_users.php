<?php
require_once '../../../config/databade.php';

// Query to fetch all users with job_role='rep'
$query = "SELECT id, username FROM signup WHERE job_role = 'rep' ORDER BY username";
$result = $conn->query($query);

$users = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            'id' => $row['id'],
            'username' => $row['username']
        ];
    }
}

// Return as JSON
header('Content-Type: application/json');
echo json_encode($users);

$conn->close();
?>
