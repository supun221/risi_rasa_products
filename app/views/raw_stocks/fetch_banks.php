<?php
require 'connection_db.php';

$query = "SELECT bank_code, bank_name FROM banks";
$result = $conn->query($query);

$banks = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $banks[] = $row;
    }
}

echo json_encode($banks);

$conn->close();
?>
