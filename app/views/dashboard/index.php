<?php
require_once '../header1.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
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
        <button id="invoiceButton" class="button-style">
            <img src="../../assets/images/invoices.png" alt="Create Invoice">
            <span>Create Invoice</span>
        </button>
        <button onclick="location.href='branch_sales.php';">
            <img src="../../assets/images/order.png" alt="Sales Order">
            <span>brach Sales </span>
        </button>
        <!-- <button onclick="loadSection('low_stock', '#dynamic-section')">
            <img src="../../assets/images/Quatation.png" alt="Quotation">
            <span>Quotation</span>
        </button> -->
        <button onclick="location.href='../inventory/stock_view.php';">
            <img src="../../assets/images/Stock.png" alt="Stock">
            <span>Stock</span>
        </button>
        <button onclick="location.href='../customers/customer_list.php';">
            <img src="../../assets/images/details.png" alt="Customer">
            <span>Customer</span>
        </button>
        <button onclick="location.href='../inventory/manage_inventory.php';">
            <img src="../../assets/images/customer.png" alt="GRN/Purchasing">
            <span>GRN/ Purchasing</span>
        </button>
        <button onclick="location.href='../raw_stocks/manage_raw_stock.php';">
            <img src="../../assets/images/customer.png" alt="Raw Stock">
            <span>Raw Stock</span>
        </button>
        <button onclick="loadSection('bank', '#dynamic-section')">
            <img src="../../assets/images/bank.png" alt="Bank">
            <span>Bank</span>
        </button>
        <button onclick="location.href='../employees/employee_list.php';">
            <img src="../../assets/images/office-man.png" alt="Customer">
            <span>Employees</span>
        </button>
        <button onclick="loadSection('cash_book', '#dynamic-section')">
            <img src="../../assets/images/money.png" alt="Cash Book">
            <span>Cash Book</span>
        </button>
        <button onclick="loadSection('expenses', '#dynamic-section')">
            <img src="../../assets/images/expenses.png" alt="Expenses">
            <span>Expenses</span>
        </button>
        <button onclick="location.href='../suppliers/suppliers_list.php';">
            <img src="../../assets/images/supplier.png" alt="Suppliers">
            <span>Suppliers</span>
        </button>
        <button onclick="location.href='../damage/damage_list.php';">
            <img src="../../assets/images/damage.png" alt="damage">
            <span>Damage & Lost</span>
        </button>
        <button onclick="location.href='../print-dashboard/print_dashboard.php';">
            <img src="../../assets/images/report.png" alt="Report">
            <span>Report</span>
        </button>
        <button onclick="location.href='../users/users_list.php';">
            <img src="../../assets/images/office-man.png" alt="User">
            <span>User</span>
        </button>
        <button onclick="location.href='../production';">
            <img src="../../assets/images/production.png" alt="Setting">
            <span>Production</span>
        </button>
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