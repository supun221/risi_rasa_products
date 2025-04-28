<?php
session_start();
require_once '../../../config/databade.php';

$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if ($user_role !== 'admin') {
    header('Location: ../unauthorized/unauthorized_access.php');
    exit;
}

$username = isset($_GET['username']) ? $_GET['username'] : '';
$query = "SELECT * FROM signup WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    die("User not found.");
}

// Fetch current permissions
$perm_query = "SELECT * FROM user_permissions WHERE username = ?";
$perm_stmt = $conn->prepare($perm_query);
$perm_stmt->bind_param("s", $username);
$perm_stmt->execute();
$perm_result = $perm_stmt->get_result();
$permissions = $perm_result->fetch_assoc();

if (!$permissions) {
    // Insert default permissions if none exist
    $insert_query = "INSERT INTO user_permissions (username) VALUES (?)";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("s", $username);
    $insert_stmt->execute();
    $permissions = [
        'username' => $username,
        'can_edit_price' => 0,
        'can_edit_discount' => 0,
        'can_edit_free_issue' => 0,
        'can_view_wholesale' => 0,
        'can_view_scg_price' => 0,
        'can_view_cost' => 0,
        'can_view_rem_stock' => 0,
        'can_view_employees' => 0,
        'can_view_reports' => 0,
        'can_view_users' => 0,
        'can_view_stock' => 0,
        'can_create_invoice' => 0,        // New
        'can_view_sales_order' => 0,      // New
        'can_view_quotation' => 0,        // New
        'can_view_customer' => 0,         // New
        'can_view_grn_purchasing' => 0,   // New
        'can_view_bank' => 0,             // New
        'can_view_cash_book' => 0,        // New
        'can_view_expenses' => 0,         // New
        'can_view_suppliers' => 0,        // New
        'can_view_damage_lost' => 0,      // New
        'can_view_settings' => 0          // New
    ];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $can_edit_price = isset($_POST['can_edit_price']) ? 1 : 0;
    $can_edit_discount = isset($_POST['can_edit_discount']) ? 1 : 0;
    $can_edit_free_issue = isset($_POST['can_edit_free_issue']) ? 1 : 0;
    $can_view_wholesale = isset($_POST['can_view_wholesale']) ? 1 : 0;
    $can_view_scg_price = isset($_POST['can_view_scg_price']) ? 1 : 0;
    $can_view_cost = isset($_POST['can_view_cost']) ? 1 : 0;
    $can_view_rem_stock = isset($_POST['can_view_rem_stock']) ? 1 : 0;
    $can_view_employees = isset($_POST['can_view_employees']) ? 1 : 0;
    $can_view_reports = isset($_POST['can_view_reports']) ? 1 : 0;
    $can_view_users = isset($_POST['can_view_users']) ? 1 : 0;
    $can_view_stock = isset($_POST['can_view_stock']) ? 1 : 0;
    $can_create_invoice = isset($_POST['can_create_invoice']) ? 1 : 0;         // New
    $can_view_sales_order = isset($_POST['can_view_sales_order']) ? 1 : 0;    // New
    $can_view_quotation = isset($_POST['can_view_quotation']) ? 1 : 0;        // New
    $can_view_customer = isset($_POST['can_view_customer']) ? 1 : 0;          // New
    $can_view_grn_purchasing = isset($_POST['can_view_grn_purchasing']) ? 1 : 0; // New
    $can_view_bank = isset($_POST['can_view_bank']) ? 1 : 0;                  // New
    $can_view_cash_book = isset($_POST['can_view_cash_book']) ? 1 : 0;        // New
    $can_view_expenses = isset($_POST['can_view_expenses']) ? 1 : 0;          // New
    $can_view_suppliers = isset($_POST['can_view_suppliers']) ? 1 : 0;        // New
    $can_view_damage_lost = isset($_POST['can_view_damage_lost']) ? 1 : 0;    // New
    $can_view_settings = isset($_POST['can_view_settings']) ? 1 : 0;          // New

    $update_query = "UPDATE user_permissions SET 
        can_edit_price = ?, can_edit_discount = ?, can_edit_free_issue = ?, 
        can_view_wholesale = ?, can_view_scg_price = ?, can_view_cost = ?, 
        can_view_rem_stock = ?, can_view_employees = ?, can_view_reports = ?, 
        can_view_users = ?, can_view_stock = ?, can_create_invoice = ?, 
        can_view_sales_order = ?, can_view_quotation = ?, can_view_customer = ?, 
        can_view_grn_purchasing = ?, can_view_bank = ?, can_view_cash_book = ?, 
        can_view_expenses = ?, can_view_suppliers = ?, can_view_damage_lost = ?, 
        can_view_settings = ? 
        WHERE username = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param(
        "sssssssssssssssssssssss",
        $can_edit_price, $can_edit_discount, $can_edit_free_issue,
        $can_view_wholesale, $can_view_scg_price, $can_view_cost,
        $can_view_rem_stock, $can_view_employees, $can_view_reports,
        $can_view_users, $can_view_stock, $can_create_invoice,
        $can_view_sales_order, $can_view_quotation, $can_view_customer,
        $can_view_grn_purchasing, $can_view_bank, $can_view_cash_book,
        $can_view_expenses, $can_view_suppliers, $can_view_damage_lost,
        $can_view_settings, $username
    );
    $update_stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Permissions</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .permissions-table th, .permissions-table td {
            padding: 10px;
            text-align: center;
        }
        .permissions-table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 60%;
        }
        button {
            background-color: rgb(39, 56, 207);
            color: white;
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: rgb(45, 7, 116);
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #0056b3;
        }
        .section-header {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>User: <?php echo htmlspecialchars($user['username']); ?></h1>
    <p>Store: <?php echo htmlspecialchars($user['store']); ?></p>
    <p>Email: <?php echo htmlspecialchars($user['Email']); ?></p>
    <p>Telephone: <?php echo htmlspecialchars($user['telephone']); ?></p>

    <h2>Manage Permissions</h2>
    <form method="POST" id="permissionsForm">
        <table class="permissions-table">
            <thead>
                <tr>
                    <th>Permission</th>
                    <th>Allow</th>
                </tr>
            </thead>
            <tbody>
                <!-- Edit Permissions -->
                <tr><td class="section-header" colspan="2">Edit Permissions</td></tr>
                <tr><td>Edit Our Price</td><td><input type="checkbox" name="can_edit_price" value="1" <?php echo $permissions['can_edit_price'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>Edit Discount</td><td><input type="checkbox" name="can_edit_discount" value="1" <?php echo $permissions['can_edit_discount'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>Edit Free Issue</td><td><input type="checkbox" name="can_edit_free_issue" value="1" <?php echo $permissions['can_edit_free_issue'] ? 'checked' : ''; ?>></td></tr>

                <!-- View Permissions -->
                <tr><td class="section-header" colspan="2">View Permissions</td></tr>
                <tr><td>View Wholesale Price</td><td><input type="checkbox" name="can_view_wholesale" value="1" <?php echo $permissions['can_view_wholesale'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View SCG Price</td><td><input type="checkbox" name="can_view_scg_price" value="1" <?php echo $permissions['can_view_scg_price'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Cost</td><td><input type="checkbox" name="can_view_cost" value="1" <?php echo $permissions['can_view_cost'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Remaining Stock</td><td><input type="checkbox" name="can_view_rem_stock" value="1" <?php echo $permissions['can_view_rem_stock'] ? 'checked' : ''; ?>></td></tr>
             
                <!-- Dashboard Features -->
                <tr><td class="section-header" colspan="2">Dashboard Features</td></tr>
                <tr><td>View Employees</td><td><input type="checkbox" name="can_view_employees" value="1" <?php echo $permissions['can_view_employees'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Reports</td><td><input type="checkbox" name="can_view_reports" value="1" <?php echo $permissions['can_view_reports'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Users</td><td><input type="checkbox" name="can_view_users" value="1" <?php echo $permissions['can_view_users'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Stock</td><td><input type="checkbox" name="can_view_stock" value="1" <?php echo $permissions['can_view_stock'] ? 'checked' : ''; ?>></td></tr>

                <tr><td>Create Invoice</td><td><input type="checkbox" name="can_create_invoice" value="1" <?php echo $permissions['can_create_invoice'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Sales Order</td><td><input type="checkbox" name="can_view_sales_order" value="1" <?php echo $permissions['can_view_sales_order'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Quotation</td><td><input type="checkbox" name="can_view_quotation" value="1" <?php echo $permissions['can_view_quotation'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Customer</td><td><input type="checkbox" name="can_view_customer" value="1" <?php echo $permissions['can_view_customer'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View GRN/Purchasing</td><td><input type="checkbox" name="can_view_grn_purchasing" value="1" <?php echo $permissions['can_view_grn_purchasing'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Bank</td><td><input type="checkbox" name="can_view_bank" value="1" <?php echo $permissions['can_view_bank'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Cash Book</td><td><input type="checkbox" name="can_view_cash_book" value="1" <?php echo $permissions['can_view_cash_book'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Expenses</td><td><input type="checkbox" name="can_view_expenses" value="1" <?php echo $permissions['can_view_expenses'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Suppliers</td><td><input type="checkbox" name="can_view_suppliers" value="1" <?php echo $permissions['can_view_suppliers'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Damage & Lost</td><td><input type="checkbox" name="can_view_damage_lost" value="1" <?php echo $permissions['can_view_damage_lost'] ? 'checked' : ''; ?>></td></tr>
                <tr><td>View Settings</td><td><input type="checkbox" name="can_view_settings" value="1" <?php echo $permissions['can_view_settings'] ? 'checked' : ''; ?>></td></tr>
            </tbody>
        </table>
        <button type="submit">Save Permissions</button>
    </form>

    <a href="users_list.php" class="back-link">Back to Dashboard</a>

    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Permissions updated successfully!',
                timer: 1500,
                showConfirmButton: false
            }).then(() => {
                window.location.href = 'users_list.php';
            });
        </script>
    <?php endif; ?>
</body>
</html>