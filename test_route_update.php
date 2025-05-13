<?php
require_once 'config/databade.php';

// Create logs directory if it doesn't exist
if (!file_exists('logs')) {
    mkdir('logs', 0777, true);
}

// Simple logging function
function log_message($message) {
    file_put_contents("logs/route_test.log", date('Y-m-d H:i:s') . ": $message\n", FILE_APPEND);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $customer_id = mysqli_real_escape_string($conn, $_POST['customer_id'] ?? '');
    $route_id = isset($_POST['route_id']) && $_POST['route_id'] !== '' ? mysqli_real_escape_string($conn, $_POST['route_id']) : null;
    
    log_message("Form submitted - Customer ID: $customer_id, Route ID: " . var_export($route_id, true));
    
    // Update the customer's route_id
    $sql = "UPDATE customers SET route_id = " . ($route_id === null ? "NULL" : "'$route_id'") . " WHERE id = '$customer_id'";
    log_message("SQL: $sql");
    
    if (mysqli_query($conn, $sql)) {
        $message = "Customer route updated successfully!";
        log_message("Success - Updated customer $customer_id route to " . var_export($route_id, true));
        
        // Verify the update
        $verify_sql = "SELECT route_id FROM customers WHERE id = '$customer_id'";
        $verify_result = mysqli_query($conn, $verify_sql);
        $verify_row = mysqli_fetch_assoc($verify_result);
        log_message("Verification - Saved route_id: " . var_export($verify_row['route_id'], true));
    } else {
        $error = "Error updating customer route: " . mysqli_error($conn);
        log_message("Error: " . $error);
    }
}

// Get customers for dropdown
$customers_query = "SELECT id, name FROM customers ORDER BY name";
$customers_result = mysqli_query($conn, $customers_query);
$customers = [];
while ($row = mysqli_fetch_assoc($customers_result)) {
    $customers[] = $row;
}

// Get routes for dropdown
$routes_query = "SELECT id, name FROM routes ORDER BY name";
$routes_result = mysqli_query($conn, $routes_query);
$routes = [];
while ($row = mysqli_fetch_assoc($routes_result)) {
    $routes[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Test Route Update</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        select { padding: 6px; width: 300px; }
        button { padding: 8px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .success { color: green; padding: 10px; background: #e8f5e9; border: 1px solid #c8e6c9; margin: 10px 0; }
        .error { color: red; padding: 10px; background: #ffebee; border: 1px solid #ffcdd2; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Test Route Update</h1>
        
        <?php if (isset($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="form-group">
                <label for="customer_id">Customer:</label>
                <select name="customer_id" id="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?php echo $customer['id']; ?>"><?php echo htmlspecialchars($customer['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="route_id">Route:</label>
                <select name="route_id" id="route_id">
                    <option value="">-- No Route --</option>
                    <?php foreach ($routes as $route): ?>
                        <option value="<?php echo $route['id']; ?>"><?php echo htmlspecialchars($route['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit">Update Route</button>
        </form>
        
        <h2>Current Customer Routes</h2>
        <table>
            <tr>
                <th>Customer ID</th>
                <th>Customer Name</th>
                <th>Route ID</th>
                <th>Route Name</th>
            </tr>
            <?php
            $customer_routes_query = "SELECT c.id, c.name, c.route_id, r.name as route_name 
                                      FROM customers c 
                                      LEFT JOIN routes r ON c.route_id = r.id 
                                      ORDER BY c.name";
            $customer_routes_result = mysqli_query($conn, $customer_routes_query);
            
            while ($row = mysqli_fetch_assoc($customer_routes_result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                    <td><?php echo $row['route_id'] ? $row['route_id'] : 'NULL'; ?></td>
                    <td><?php echo $row['route_name'] ? htmlspecialchars($row['route_name']) : '-'; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
