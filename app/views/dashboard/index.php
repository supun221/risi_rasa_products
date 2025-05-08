<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
require_once '../header1.php';
require_once '../../../config/databade.php'; // Fixed typo: 'databade' to 'database'

// Fetch user permissions
$username = $_SESSION['username'];
$query = "SELECT * FROM user_permissions WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$permissions = $stmt->get_result()->fetch_assoc();

// Default permissions if no record exists
if (!$permissions) {
    $permissions = [
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

// Check if user is admin and set all permissions to 1
$user_role = $_SESSION['job_role'];
if ($user_role === 'admin') {
    $permissions = array_map(function () {
        return 1;
    }, $permissions);
}
?>
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../../assets/css/dashboard._styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to load content dynamically into a specified section
        function loadSection(section, targetElement) {
            $.ajax({
                url: section + ".php", // Load content from corresponding PHP file
                method: "GET",
                success: function(data) {
                    $(targetElement).html(data); // Insert content into the target section
                },
                error: function() {
                    $(targetElement).html("<p>Error loading " + section + ". Please try again.</p>");
                }
            });
        }

        // Automatically load sections when the page is loaded
        $(document).ready(function() {
            loadSection('bill_records', '#bill-section'); // Load last 10 bills
            loadSection('low_stock', '#low-stock-section'); // Load low stock items
            loadSection('expire_notification', '#employee-section'); // Load employee list
            loadSection('fast_moving_products', '#fastmoving-section'); // Load employee list
        });
    </script>
    <style>
        /* Basic styles for buttons */
        .button-bar button {
            margin: 4px;
            padding: 2px;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Ensure equal sizes for all sections */
        .top-sections,
        .middle-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            /* Two columns of equal width */
            gap: 10px;
            margin-bottom: 10px;
        }

        .section {
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fafafa;
            height: 250px;
            /* Fixed height for all sections */
            overflow: auto;
            /* Enable scrolling for overflow content */
        }

        .section h2 {
            margin-bottom: 10px;
        }

        #dynamic-section {
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?>!</h1>

    <!-- Button Bar -->
    <div class="button-bar">
        <?php if ($permissions['can_create_invoice']): ?>
            <button id="invoiceButton" class="button-style">
                <img src="../../assets/images/invoices.png" alt="Create Invoice">
                <span>Create Invoice</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_sales_order']): ?>
            <button onclick="location.href='Return_rep_order.php'">
                <img src="../../assets/images/order.png" alt="Sales Order">
                <span>Sales Order return</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_quotation']): ?>
            <button onclick="location.href='quotations.php';">
                <img src="../../assets/images/Quatation.png" alt="Quotation">
                <span>Quotation</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_stock']): ?>
            <button onclick="location.href='../inventory/stock_view.php';">
                <img src="../../assets/images/Stock.png" alt="Stock">
                <span>Stock</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_customer']): ?>
            <button onclick="location.href='../customers/customer_list.php';">
                <img src="../../assets/images/details.png" alt="Customer">
                <span>Customer</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_grn_purchasing']): ?>
            <button onclick="location.href='../inventory/manage_inventory.php';">
                <img src="../../assets/images/customer.png" alt="GRN/Purchasing">
                <span>GRN/Purchasing</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_bank']): ?>
            <button onclick="location.href='../bank/Bank_Account.php';">
                <img src="../../assets/images/bank.png" alt="Bank">
                <span>Bank</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_employees']): ?>
            <button onclick="location.href='../employees/employee_list.php';">
                <img src="../../assets/images/office-man.png" alt="Employees">
                <span>Employees</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_cash_book']): ?>
            <button onclick="location.href='../cashbook/cashbook_list.php';">
                <img src="../../assets/images/money.png" alt="Cash Book">
                <span>Cash Book</span>
            </button>
        <?php endif; ?>

        <!-- <?php if ($permissions['can_view_expenses']): ?>
            <button onclick="loadSection('expenses', '#dynamic-section')">
                <img src="../../assets/images/expenses.png" alt="Expenses">
                <span>Expenses</span>
            </button>
        <?php endif; ?> -->

        <?php if ($permissions['can_view_suppliers']): ?>
            <button onclick="location.href='../suppliers/suppliers_list.php';">
                <img src="../../assets/images/supplier.png" alt="Suppliers">
                <span>Suppliers</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_damage_lost']): ?>
            <button onclick="location.href='../damage/damage_list.php';">
                <img src="../../assets/images/damage.png" alt="Damage">
                <span>Damage & Lost</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_reports']): ?>
            <button onclick="location.href='../print-dashboard/print_dashboard.php';">
                <img src="../../assets/images/report.png" alt="Report">
                <span>Report</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_users']): ?>
            <button onclick="location.href='../users/users_list.php';">
                <img src="../../assets/images/office-man.png" alt="User">
                <span>User</span>
            </button>
        <?php endif; ?>

        <?php if ($permissions['can_view_settings']): ?>
            <button onclick="loadSection('setting', '#dynamic-section')">
                <img src="../../assets/images/settings.png" alt="Setting">
                <span>Setting</span>
            </button>
        <?php endif; ?>

        <!-- Always show logout -->
        <button onclick="location.href='../../controllers/logout.php';" class="button-style">
            <img src="../../assets/images/power.png" alt="Log Out">
            <span>Log Out</span>
        </button>
    </div>

    <!-- Top Sections: Last 10 Bills and Low Stock -->
    <div class="top-sections">
        <!-- Section for Last 10 Bills -->
        <div id="bill-section" class="section">
            <h2>Last 10 Bills</h2>
            <p>Loading...</p>
        </div>

        <!-- Section for Low Stock -->
        <div id="low-stock-section" class="section">
            <h2>Low Stock Items</h2>
            <p>Loading...</p>
        </div>
    </div>
    <div class="middle-section">
        <!-- Section for Employee List -->
        <div id="employee-section" class="section">
            <h2>Employee List</h2>
            <p>Loading...</p>
        </div>
        <div id="fastmoving-section" class="section">
            <h2>fastmoving List</h2>
            <p>Loading...</p>
        </div>
    </div>

    <!-- Dynamic Section (used by other buttons) -->
    <div id="dynamic-section" class="section" style="display: none;">
        <p>Dynamic content will load here...</p>
    </div>
</body>

<script>
    // Get the button element
    document.getElementById('invoiceButton').addEventListener('click', function() {
        location.href = '../invoice/opening_balance.php';
    });
</script>

</html>