<?php
// Connect to database
require_once '../../../config/databade.php';

// Initialize response array
$response = [];

try {
    // Query the signup table (not users table) for reps
    $query = "SELECT id, username FROM signup WHERE job_role = 'rep' ORDER BY username";
    $result = mysqli_query($conn, $query);
    
    if ($result) {
        // Fetch rep users
        while ($row = mysqli_fetch_assoc($result)) {
            $response[] = [
                'id' => $row['id'],
                'username' => $row['username']
            ];
        }
    } else {
        // Log error but don't expose in response
        error_log("Error querying signup table: " . mysqli_error($conn));
    }
} catch (Exception $e) {
    // Log any exception
    error_log("Exception in fetch_rep_users.php: " . $e->getMessage());
}

// Return response as JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
