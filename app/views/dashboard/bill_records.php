<?php
// File: last_10_bills.php


// Database conn
require_once '../../../config/databade.php';
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
session_start();
$user_branch = $_SESSION['store'] ?? null;

if (!$user_branch) {
    die("User branch not found. Please log in.");
}

// Fetch last 10 bills for the user's branch
$query = "SELECT bill_id, net_amount, bill_date FROM bill_records WHERE branch = ? ORDER BY bill_date DESC LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $user_branch);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Last 10 Bills</title>
    <link rel="stylesheet" href="../../assets/css/section_style.css">
</head>

<body>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            text-align: center;
        }

        h2 {
            margin-top: -20px;
            text-align: center;
            font-size: 20px;
        }

        .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 80%;
            /* Ensures the container spans the full width */
            margin: 0 auto;
            /* Centers the container itself */
        }

        table {
            width: 90%;
            /* This might need to be reconsidered for proper table layout */
            border-collapse: collapse;
            margin: 5px 0;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
            /* Adds shadow effect */
        }

        table th,
        table td {
            padding: 5px 5px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: hsl(229, 98%, 81%);
            border: none;
            ;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
            /* Row hover effect */
        }
    </style>
    <h2>Last 10 Bills</h2>
    <table>
        <thead>
            <tr>
                <th>Bill ID</th>
                <th>Amount</th>
                <th>Bill Date</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>{$row['bill_id']}</td>
                            <td>{$row['net_amount']}</td>
                            <td>{$row['bill_date']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='3'>No records found</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>

</html>

<?php
// Close conn
$conn->close();
?>