<?php

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if ($user_role !== 'admin' || $user_role == null) {
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}
require_once '../header1.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Dashboard</title>
    <link rel="stylesheet" href="./print_dashboard.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="scripts.js"></script>
    <style>
        .modal-dialog {
            max-width: 90%;
            /* Adjusts the width dynamically, up to 90% of the viewport */
            width: auto;
            /* Auto width based on content */
        }

        .modal-content {
            max-height: 80vh;
            /* Limits height to 80% of viewport height */
            overflow-y: auto;
            /* Enables vertical scrolling if content overflows */
        }
    </style>
</head>

<body>
    <span class="report-page-header"> Report Export Page</span>

    <div class="report-listing-container">

        <div class="rep-generation-placeholders">
            <!-- Report Cards -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Understocked Items</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-box-open"></i></span> -->
                </div>
                <p class="report-content">Generate a report about the items which are currently in negative stock.</p>
                <button class="rep_generate_btn" onclick="generateUnderstockedItemsReport()">Generate</button>
            </div>

            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Outstanding Report</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-box-open"></i></span> -->
                </div>
                <input type="date" id="outstanding-start-date">
                <input type="date" id="outstanding-end-date">
                <p class="report-content">Generate a report about the items which are currently in negative stock.</p>
                <button class="rep_generate_btn" onclick="fetchOutstandingReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printOutstandingReport()">Print Report</button>
            </div>

            <!-- total stock -->

            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Total Stock Report</span>
                </div>
                <div class="total-stock-inputs">
                    <select id="supplier">
                        <option value="">Select Supplier</option>
                    </select>
                    <select id="category">
                        <option value="">Select Category</option>
                    </select>
                    <input type="text" id="search" placeholder="Search by Product Name or Barcode">
                    <input type="date" id="start-date">
                    <input type="date" id="end-date">
                </div>
                <button class="rep_generate_btn" onclick="fetchTotalStockReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printTotalStockReport()">Print Report</button>
            </div>

            <!-- Return report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Return Items</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-calendar-times"></i></span> -->
                </div>
                <!-- <select id="return-user">
                    <option value="">Select User</option>
                </select> -->
                <input type="date" id="return-start-date">
                <input type="date" id="return-end-date">
                <p class="report-content">Generate a report of return items.</p>
                <button class="rep_generate_btn" onclick="fetchReturnReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printReturnReport()">Print Report</button>
            </div>
            <!-- dayend report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Day End Report</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-calendar-times"></i></span> -->
                </div>
                <select id="dayend-user">
                    <option value="">Select User</option>
                </select>
                <input type="date" id="dayend-start-date">
                <input type="date" id="dayend-end-date">
                <p class="report-content">Generate a report of day end .</p>
                <button class="rep_generate_btn" onclick="fetchDayEndReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printDayEndReport()">Print Report</button>
            </div>
            <script>
                function fetchDayEndReport() {
                    let user = document.getElementById("dayend-user").value.trim();
                    let startDate = document.getElementById("dayend-start-date").value;
                    let endDate = document.getElementById("dayend-end-date").value;

                    let queryParams = new URLSearchParams({
                        user: user,
                        start_date: startDate,
                        end_date: endDate
                    });

                    let url = `fetch_day_end_report.php?${queryParams.toString()}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            $("#reportTitle").text("Day End Report");
                            $("#reportModalLabel").text("Day End Report");

                            let tableHeaders = `
                <th>Username</th>
                <th>Opening Balance</th>
                <th>Total Gross</th>
                <th>Total Net</th>
                <th>Total Discount</th>
                <th>Total Bills</th>
                <th>Total Cash</th>
                <th>Total Credit</th>
                <th>Bill Payment</th>
                <th>Cash Drawer</th>
                <th>Voucher Payment</th>
                <th>Free Payment</th>
                <th>Total Balance</th>
                <th>Day End Hand Balance</th>
                <th>Cash Balance</th>
                <th>Today Balance</th>
                <th>Difference Hand</th>
                <th>Created At</th>
            `;
                            let tableBody = "";

                            if (!data.success || data.data.length === 0) {
                                tableBody = "<tr><td colspan='18'>No records found.</td></tr>";
                            } else {
                                data.data.forEach(item => {
                                    tableBody += `
                        <tr>
                            <td>${item.username}</td>
                            <td>${item.opening_balance}</td>
                            <td>${item.total_gross}</td>
                            <td>${item.total_net}</td>
                            <td>${item.total_discount}</td>
                            <td>${item.total_bills}</td>
                            <td>${item.total_cash}</td>
                            <td>${item.total_credit}</td>
                            <td>${item.bill_payment}</td>
                            <td>${item.cash_drawer}</td>
                            <td>${item.voucher_payment}</td>
                            <td>${item.free_payment}</td>
                            <td>${item.total_balance}</td>
                            <td>${item.day_end_hand_balance}</td>
                            <td>${item.cash_balance}</td>
                            <td>${item.today_balance}</td>
                            <td>${item.difference_hand}</td>
                            <td>${item.created_at}</td>
                        </tr>
                    `;
                                });
                            }

                            $("#reportTableHead").html(tableHeaders);
                            $("#reportTableBody").html(tableBody);
                            $("#reportModal").modal("show");
                        })
                        .catch(error => console.error("Error fetching day end report:", error));
                }

                function printDayEndReport() {
                    let user = document.getElementById("dayend-user").value.trim();
                    let startDate = document.getElementById("dayend-start-date").value;
                    let endDate = document.getElementById("dayend-end-date").value;

                    let queryParams = new URLSearchParams({
                        user: user,
                        start_date: startDate,
                        end_date: endDate
                    });

                    let url = `print_day_end_report.php?${queryParams.toString()}`;
                    let printWindow = window.open(url, '_blank');

                    if (printWindow) {
                        // Use setTimeout to ensure the print dialog opens after a short delay
                        setTimeout(() => {
                            printWindow.print();
                            printWindow.onafterprint = function() {
                                printWindow.close();
                            };
                        }, 1000); // Delay of 1 second to ensure full page load
                    } else {
                        alert("Popup blocked! Allow popups for this site.");
                    }
                }
            </script>

            <!-- Sales Report (Invoice Wise) -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Sales Report (Invoice Wise)</span>
                </div>

                <label for="sales-start-date">Start Date:</label>
                <input type="date" id="sales-start-date">

                <label for="sales-end-date">End Date:</label>
                <input type="date" id="sales-end-date">

                <p class="report-content">Generate a report of sales items.</p>

                <button class="rep_generate_btn" onclick="fetchSalesReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printSalesReport()">Print Report</button>
            </div>
            
            <!-- Raw Stock Report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Raw Stock Report</span>
                </div>
                <div class="total-stock-inputs">
                    <select id="raw-stock-supplier">
                        <option value="">All Suppliers</option>
                    </select>
                    <input type="text" id="raw-stock-search" placeholder="Search by Product Name or Barcode">
                    <input type="date" id="raw-stock-start-date">
                    <input type="date" id="raw-stock-end-date">
                    <select id="raw-stock-branch">
                        <option value="">All Branches</option>
                    </select>
                </div>
                <p class="report-content">Generate a report of raw stock entries.</p>
                <button class="rep_generate_btn" onclick="fetchRawStockReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printRawStockReport()">Print Report</button>
            </div>
           <!-- Production Report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Production Report</span>
                </div>
                <div class="total-stock-inputs">
                    <input type="text" id="production-search" placeholder="Search by Product Name or Barcode">
                    <input type="date" id="production-start-date">
                    <input type="date" id="production-end-date">
                    <select id="production-branch">
                        <option value="">All Branches</option>
                    </select>
                </div>
                <p class="report-content">Generate a report of production items (supplier_id = 'self_001').</p>
                <button class="rep_generate_btn" onclick="fetchProductionReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printProductionReport()">Print Report</button>
            </div>
            <!-- Sales Report (Product Wise) -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Sales Report (Product Wise)</span>
                </div>
                <div class="total-stock-inputs">
                    <select id="sales-category">
                        <option value="">Select Category</option>
                    </select>
                    <select id="users-select-products-items">
                        <option value="">Select User</option>
                    </select>
                    <input type="text" id="sales-product-barcode" placeholder="Search by Product Barcode">
                    <input type="date" id="salesproduct-start-date">
                    <input type="date" id="salesproduct-end-date">
                </div>
                <button class="rep_generate_btn" onclick="fetchSalesProductReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printSalesProductReport()">Print Report</button>
            </div>
               <!-- Profit Report (Product Wise) -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Profit Report (Product Wise)</span>
                </div>
                <div class="total-stock-inputs">
                    <select id="profit-category">
                        <option value="">Select Category</option>
                    </select>
                    <input type="text" id="profit-product-barcode" placeholder="Search by Product Barcode">
                    <input type="date" id="profitproduct-start-date">
                    <input type="date" id="profitproduct-end-date">
                </div>
                <p class="report-content">Generate a report of product profits.</p>
                <button class="rep_generate_btn" onclick="fetchProfitProductReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printProfitProductReport()">Print Report</button>
            </div>

            <!-- Rep Sales Profit Report (Product Wise) -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Rep Sales Profit Report (Product Wise)</span>
                </div>
                <div class="total-stock-inputs">
                    <!-- <select id="profit-category">
                        <option value="">Select Category</option>
                    </select> -->
                    <input type="text" id="rep-profit-product-barcode" placeholder="Search by Product Barcode">
                    <input type="date" id="rep-profitproduct-start-date">
                    <input type="date" id="rep-profitproduct-end-date">
                </div>
                <p class="report-content">Generate a report of product profits.</p>
                <button class="rep_generate_btn" onclick="fetchRepSalesProfitProductReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printRepSalesProfitProductReport()">Print Report</button>
            </div>

            <!-- repair report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Damage Report</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-calendar-times"></i></span> -->
                </div>
                <select id="branch">
                    <option value="">Select branch</option>
                </select>
                <input type="date" id="damage-start-date">
                <input type="date" id="damage-end-date">
                <p class="report-content">Generate a report of damages .</p>
                <button class="rep_generate_btn" onclick="fetchDamage()">Run Report</button>
                <button class="rep_generate_btn" onclick="printDamage()">Print Report</button>
            </div>
            <script>
                function fetchDamage() {
                    let branch = document.getElementById("branch").value;
                    let startDate = document.getElementById("damage-start-date").value;
                    let endDate = document.getElementById("damage-end-date").value;

                    let url = `fetch_damage_report.php?branch=${encodeURIComponent(branch)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            $("#reportTitle").text("Damage Report");
                            $("#reportModalLabel").text("Damage Report");

                            let tableHeaders = `
                <th>Product Name</th>
                <th>Damage Description</th>
                <th>Damage Quantity</th>
                <th>Price</th>
                <th>date</th>
                <th>Barcode</th>
                <th>Branch</th>
            `;
                            let tableBody = "";
                            let totalDamageCost = 0;
                            let totalDamageQuantity = 0;

                            if (!data.success || data.data.length === 0) {
                                tableBody = "<tr><td colspan='6'>No damage records found.</td></tr>";
                            } else {
                                data.data.forEach(item => {
                                    totalDamageQuantity += parseInt(item.damage_quantity) || 0;
                                    totalDamageCost += parseFloat(item.price) * (parseInt(item.damage_quantity) || 0);

                                    tableBody += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.damage_description}</td>
                            <td>${item.damage_quantity}</td>
                            <td>${item.price}</td>
                            <td>${item.date}</td>
                            <td>${item.barcode || "N/A"}</td>
                            <td>${item.branch}</td>
                        </tr>
                    `;
                                });

                                // Append total row
                                tableBody += `
                    <tr style="font-weight: bold;">
                        <td colspan="2" style="text-align:right;">Total:</td>
                        <td>${totalDamageQuantity}</td>
                        <td colspan="3">${totalDamageCost.toFixed(2)}</td>
                    </tr>
                `;
                            }

                            $("#reportTableHead").html(tableHeaders);
                            $("#reportTableBody").html(tableBody);
                            $("#reportModal").modal("show");
                        })
                        .catch(error => console.error("Error fetching damage report:", error));
                }

                function printDamage() {
                    let branch = document.getElementById("branch").value;
                    let startDate = document.getElementById("dayend-start-date").value;
                    let endDate = document.getElementById("dayend-end-date").value;

                    let url = `print_damage_report.php?branch=${encodeURIComponent(branch)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

                    let printWindow = window.open(url, '_blank');

                    if (printWindow) {
                        printWindow.onload = function() {
                            printWindow.focus();
                            printWindow.print();
                        };
                    } else {
                        alert("Popup blocked! Allow popups for this site.");
                    }
                }

                function loadBranches() {
                    fetch("fetch_branch.php") // Ensure this PHP file returns the branches as JSON
                        .then(response => response.json())
                        .then(branches => {
                            let branchSelect = document.getElementById("branch");

                            // Reset dropdown with a default option
                            branchSelect.innerHTML = '<option value="">Select branch</option>';

                            // Populate dropdown with branch data
                            branches.forEach(branch => {
                                let option = `<option value="${branch}">${branch}</option>`;
                                branchSelect.innerHTML += option;
                            });
                        })
                        .catch(error => console.error("Error fetching branches:", error));
                }

                // Call the function when the page loads
                document.addEventListener("DOMContentLoaded", loadBranches);

                function loadUsers() {
                    fetch("fetch_users.php")
                        .then(response => response.json())
                        .then(users => {
                            let userSelectDeleteItems = document.getElementById("users-select-delete-items");
                            let userSelectDeleteBills = document.getElementById("users-select-delete-bills");
                            let userSelectProducts = document.getElementById("users-select-products-items"); // Added
                            let userSelectDayend = document.getElementById("dayend-user"); // Added for new dropdown

                            // Reset dropdowns with a default option
                            userSelectDeleteItems.innerHTML = '<option value="">All User</option>';
                            userSelectDeleteBills.innerHTML = '<option value="">All User</option>';
                            userSelectProducts.innerHTML = '<option value="">All User</option>'; // Reset added dropdown
                            userSelectDayend.innerHTML = '<option value="">Select User</option>'; // Reset new dropdown

                            // Populate all dropdowns with user data
                            users.forEach(user => {
                                let option = `<option value="${user}">${user}</option>`;
                                userSelectDeleteItems.innerHTML += option;
                                userSelectDeleteBills.innerHTML += option;
                                userSelectProducts.innerHTML += option; // Added for report dropdown
                                userSelectDayend.innerHTML += option; // Added for dayend-user dropdown
                            });
                        })
                        .catch(error => console.error("Error fetching users:", error));
                }

                function fetchSalesProductReport() {
                    let category = document.getElementById("sales-category").value;
                    let barcode = document.getElementById("sales-product-barcode").value;
                    let productName = document.getElementById("sales-product-name") ? document.getElementById("sales-product-name").value : "";
                    let startDate = document.getElementById("salesproduct-start-date").value;
                    let endDate = document.getElementById("salesproduct-end-date").value;
                    let issuer = document.getElementById("users-select-products-items").value;

                    let url = `fetch_sales_product_report.php?category=${encodeURIComponent(category)}&barcode=${encodeURIComponent(barcode)}&product_name=${encodeURIComponent(productName)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&issuer=${encodeURIComponent(issuer)}`;

                    fetch(url)
                        .then(response => response.json())
                        .then(data => {
                            $("#reportTitle").text("Sales Report (Product Wise)");
                            $("#reportModalLabel").text("Sales Report (Product Wise)");

                            let tableHeaders = `
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Category</th>
                <th>Price</th>
                <th>Total Quantity</th>
                <th>Discount (%)</th>
                <th>Total Subtotal</th>
                <th>Purchased Date</th>
                <th>Issuer</th>
            `;
                            let tableBody = "";
                            let totalQuantity = 0;
                            let totalSubtotal = 0;

                            if (!data.success || data.data.length === 0) {
                                tableBody = "<tr><td colspan='9'>No sales records found.</td></tr>";
                            } else {
                                data.data.forEach(item => {
                                    totalQuantity += parseFloat(item.total_qty) || 0;
                                    totalSubtotal += parseFloat(item.total_subtotal) || 0;

                                    tableBody += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.product_barcode}</td>
                            <td>${item.category}</td>
                            <td>${item.price}</td>
                            <td>${item.total_qty}</td>
                            <td>${item.discount_percentage || "0.00"}</td>
                            <td>${item.total_subtotal}</td>
                            <td>${item.purchased_date}</td>
                            <td>${item.user || "N/A"}</td>
                        </tr>
                    `;
                                });

                                // Append total row
                                tableBody += `
                    <tr style="font-weight: bold;">
                        <td colspan="4" style="text-align:right;">Total:</td>
                        <td>${totalQuantity}</td>
                        <td>-</td>
                        <td>${totalSubtotal.toFixed(2)}</td>
                        <td>-</td>
                        <td>-</td>
                    </tr>
                `;

                                // Append cash and card totals row
                                tableBody += `
                    <tr style="font-weight: bold; background: #f0f0f0;">
                        <td colspan="6" style="text-align:right;">Total Cash Sales:</td>
                        <td>${parseFloat(data.total_cash).toFixed(2)}</td>
                        <td colspan="2"></td>
                    </tr>
                    <tr style="font-weight: bold; background: #f0f0f0;">
                        <td colspan="6" style="text-align:right;">Total Card Sales:</td>
                        <td>${parseFloat(data.total_card).toFixed(2)}</td>
                        <td colspan="2"></td>
                    </tr>
                `;
                            }

                            $("#reportTableHead").html(tableHeaders);
                            $("#reportTableBody").html(tableBody);
                            $("#reportModal").modal("show");
                        })
                        .catch(error => console.error("Error fetching sales report:", error));
                }

                function printSalesProductReport() {
                    let category = document.getElementById("sales-category").value;
                    let barcode = document.getElementById("sales-product-barcode").value;
                    let startDate = document.getElementById("salesproduct-start-date").value;
                    let endDate = document.getElementById("salesproduct-end-date").value;
                    let issuer = document.getElementById("users-select-products-items").value;


                    let url = `print_sales_product_report.php?category=${encodeURIComponent(category)}&barcode=${encodeURIComponent(barcode)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&issuer=${encodeURIComponent(issuer)}`;

                    let printWindow = window.open(url, '_blank');

                    if (printWindow) {
                        printWindow.onload = function() {
                            printWindow.focus();
                            // Automatically trigger the print dialog
                            printWindow.print();
                        };
                    } else {
                        alert("Popup blocked! Allow popups for this site.");
                    }
                }

                // Fetch categories for the sales report
                function fetchCategoriesForSalesReport() {
                    fetch("../dashboard/get_categories.php")
                        .then(response => response.json())
                        .then(data => {
                            let categorySelect = document.getElementById("sales-category");
                            categorySelect.innerHTML = '<option value="">All Category</option>';
                            data.forEach(category => {
                                categorySelect.innerHTML += `<option value="${category}">${category}</option>`;
                            });
                        })
                        .catch(error => console.error("Error fetching categories:", error));
                }

                const generateUnderstockedItemsReport = () => {
                    window.open('../understocked_item_report/understocked_item_report.php')
                }
                // Load categories when the page loads
                document.addEventListener("DOMContentLoaded", function() {
                    fetchCategoriesForSalesReport();
                });
            </script>


            <!-- Delete bill items report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Delete Bill Items</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-calendar-times"></i></span> -->
                </div>
                <select id="users-select-delete-items">
                    <option value="">Select User</option>
                </select>
                <input type="date" id="delete-start-date">
                <input type="date" id="delete-end-date">
                <p class="report-content">Generate a report of delete bill items.</p>
                <button class="rep_generate_btn" onclick="fetchDeleteBillItemsReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printDeleteBillItemsReport()">Print Report</button>
            </div>
            <!-- Delete bills report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Delete Bills</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-calendar-times"></i></span> -->
                </div>
                <select id="users-select-delete-bills">
                    <option value="">Select User</option>
                </select>
                <input type="date" id="deletebill-start-date">
                <input type="date" id="deletebill-end-date">
                <p class="report-content">Generate a report of delete bills.</p>
                <button class="rep_generate_btn" onclick="fetchDeleteBillReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printDeleteBillReport()">Print Report</button>
            </div>

            <!-- Expired reports -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Expired Items</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-calendar-times"></i></span> -->
                </div>
                <input type="date" id="expire-start-date">
                <input type="date" id="expire-end-date">
                <p class="report-content">Generate a report of expired inventory items.</p>
                <button class="rep_generate_btn" onclick="fetchExpireReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printExpireReport()">Print Report</button>
            </div>
            <!-- Low stock -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Low Stock</span>
                    <!-- <span class="report-placeholder-icon"><i class="fa-solid fa-receipt"></i></span> -->
                </div>
                <label for="low-category">Select Category:</label>
                <select id="low-category">
                    <option value="">Select Category</option>
                </select>
                <input type="text" id="search-low" placeholder="Search by Product Name or Barcode">
                <p class="report-content">Generate a report of customer orders.</p>
                <button class="rep_generate_btn" onclick="fetchLowStockReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printLowStockReport()">Print Report</button>
            </div>
            
            <!-- Rep Payments Report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Rep Payments</span>
                </div>
                <label for="rep-payments-username">Select Rep:</label>
                <select id="rep-payments-username">
                    <option value="">All Reps</option>
                </select>
                <label for="rep-payments-start-date">Start Date:</label>
                <input type="date" id="rep-payments-start-date">
                <label for="rep-payments-end-date">End Date:</label>
                <input type="date" id="rep-payments-end-date">
                <p class="report-content">Generate a report of payments collected by reps.</p>
                <button class="rep_generate_btn" onclick="fetchRepPaymentsReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printRepPaymentsReport()">Print Report</button>
            </div>
            
            <!-- Rep Sales Items Report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Rep Sales Items</span>
                </div>
                <label for="rep-sales-username">Select Rep:</label>
                <select id="rep-sales-username">
                    <option value="">All Reps</option>
                </select>
                <label for="rep-sales-route">Select Route:</label>
                <select id="rep-sales-route">
                    <option value="">All Routes</option>
                </select>
                <label for="rep-sales-start-date">Start Date:</label>
                <input type="date" id="rep-sales-start-date">
                <label for="rep-sales-end-date">End Date:</label>
                <input type="date" id="rep-sales-end-date">
                <label for="rep-sales-barcode">Barcode:</label>
                <input type="text" id="rep-sales-barcode" placeholder="Search by Product Barcode">
                <p class="report-content">Generate a report of sales items by reps.</p>
                <button class="rep_generate_btn" onclick="fetchRepSalesReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printRepSalesReport()">Print Report</button>
            </div>
            
            <!-- Rep Lorry Stock Report -->
            <div class="report-btn-container">
                <div class="report-heading-cont">
                    <span class="report-name">Rep Lorry Stock</span>
                </div>
                <label for="rep-stock-username">Select Rep:</label>
                <select id="rep-stock-username">
                    <option value="">All Reps</option>
                </select>
                <label for="rep-stock-start-date">Start Date:</label>
                <input type="date" id="rep-stock-start-date">
                <label for="rep-stock-end-date">End Date:</label>
                <input type="date" id="rep-stock-end-date">
                <label for="rep-stock-barcode">Barcode:</label>
                <input type="text" id="rep-stock-barcode" placeholder="Search by Product Barcode">
                <p class="report-content">Generate a report of lorry stock status for reps.</p>
                <button class="rep_generate_btn" onclick="fetchRepLorryStockReport()">Run Report</button>
                <button class="rep_generate_btn" onclick="printRepLorryStockReport()">Print Report</button>
            </div>
        </div>
    </div>

    <!-- Reusable Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reportModalLabel">Report</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <h4 id="reportTitle" class="text-center"></h4>
                    <table class="table table-bordered mt-3">
                        <thead>
                            <tr id="reportTableHead">
                                <!-- Headers will be inserted dynamically -->
                            </tr>
                        </thead>
                        <tbody id="reportTableBody">
                            <!-- Data will be inserted dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <!-- <button class="btn btn-success" id="exportExcel">Export to Excel</button> -->
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
<script>
    document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });
    
    // Load rep usernames when page loads
    document.addEventListener("DOMContentLoaded", function() {
        fetchCategoriesForSalesReport();
        loadUsers();
        loadRepUsernames();
        loadRoutes();
        loadSuppliers();
        loadBranchesForProduction(); // Add this new function call
    });
    
    // Function to load branches for production report
    function loadBranchesForProduction() {
        fetch("fetch_branch.php")
            .then(response => response.json())
            .then(branches => {
                let branchSelect = document.getElementById("production-branch");
                
                // Reset dropdown with default option
                branchSelect.innerHTML = '<option value="">All Branches</option>';
                
                // Populate dropdown with branch data
                branches.forEach(branch => {
                    let option = `<option value="${branch}">${branch}</option>`;
                    branchSelect.innerHTML += option;
                });
            })
            .catch(error => console.error("Error fetching branches for production:", error));
    }
    
    // Production Report Functions
    function fetchProductionReport() {
        let search = document.getElementById("production-search").value;
        let startDate = document.getElementById("production-start-date").value;
        let endDate = document.getElementById("production-end-date").value;
        let branch = document.getElementById("production-branch").value;
        
        let queryParams = new URLSearchParams({
            search: search,
            start_date: startDate,
            end_date: endDate,
            branch: branch
        });
        
        let url = `fetch_production_report.php?${queryParams.toString()}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                $("#reportTitle").text("Production Report");
                $("#reportModalLabel").text("Production Report");
                
                let tableHeaders = `
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Production Quantity</th>
                    <th>Unit</th>
                    <th>Cost Price</th>
                    <th>Total Cost</th>
                    <th>MRP</th>
                    <th>Branch</th>
                    <th>Production Date</th>
                `;
                
                let tableBody = "";
                let totalQuantity = 0;
                let totalCost = 0;
                
                if (!data.success || data.data.length === 0) {
                    tableBody = "<tr><td colspan='9'>No production records found.</td></tr>";
                } else {
                    data.data.forEach(item => {
                        totalQuantity += parseInt(item.purchase_qty) || 0;
                        totalCost += parseFloat(item.total_cost_amount) || 0;
                        
                        tableBody += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.barcode || "N/A"}</td>
                            <td>${item.purchase_qty}</td>
                            <td>${item.unit || "N/A"}</td>
                            <td>${parseFloat(item.cost_price).toFixed(2)}</td>
                            <td>${parseFloat(item.total_cost_amount).toFixed(2)}</td>
                            <td>${parseFloat(item.max_retail_price).toFixed(2)}</td>
                            <td>${item.branch}</td>
                            <td>${item.created_at}</td>
                        </tr>
                    `;
                    });
                    
                    // Append total row
                    tableBody += `
                    <tr style="font-weight: bold;">
                        <td colspan="2" style="text-align:right;">Total:</td>
                        <td>${totalQuantity}</td>
                        <td></td>
                        <td></td>
                        <td>${totalCost.toFixed(2)}</td>
                        <td colspan="3"></td>
                    </tr>
                `;
                }
                
                $("#reportTableHead").html(tableHeaders);
                $("#reportTableBody").html(tableBody);
                $("#reportModal").modal("show");
            })
            .catch(error => console.error("Error fetching production report:", error));
    }
    
    function printProductionReport() {
        let search = document.getElementById("production-search").value;
        let startDate = document.getElementById("production-start-date").value;
        let endDate = document.getElementById("production-end-date").value;
        let branch = document.getElementById("production-branch").value;
        
        let url = `print_production_report.php?search=${encodeURIComponent(search)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&branch=${encodeURIComponent(branch)}`;
        
        let printWindow = window.open(url, '_blank');
        
        if (printWindow) {
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }, 1000);
        } else {
            alert("Popup blocked! Allow popups for this site.");
        }
    }
    
    // Function to load rep usernames for the dropdown lists
    function loadRepUsernames() {
        fetch("fetch_rep_users.php")
            .then(response => response.json())
            .then(users => {
                let repPaymentsSelect = document.getElementById("rep-payments-username");
                let repSalesSelect = document.getElementById("rep-sales-username");
                let repStockSelect = document.getElementById("rep-stock-username");
                
                // Reset dropdowns with default options
                repPaymentsSelect.innerHTML = '<option value="">All Reps</option>';
                repSalesSelect.innerHTML = '<option value="">All Reps</option>';
                repStockSelect.innerHTML = '<option value="">All Reps</option>';
                
                // Check if users is an array (might be an empty array)
                if (Array.isArray(users)) {
                    // Populate dropdowns with user data
                    users.forEach(user => {
                        let option = `<option value="${user.id}">${user.username}</option>`;
                        repPaymentsSelect.innerHTML += option;
                        repSalesSelect.innerHTML += option;
                        repStockSelect.innerHTML += option;
                    });
                } else {
                    console.log("No rep users found or invalid response format");
                }
            })
            .catch(error => {
                console.error("Error fetching rep usernames:", error);
                // The dropdowns will just have "All Reps" option
            });
    }
    
    // Function to load routes for dropdown
    function loadRoutes() {
        fetch("fetch_routes.php")
            .then(response => response.json())
            .then(routes => {
                let repSalesRouteSelect = document.getElementById("rep-sales-route");
                
                // Reset dropdown with default option
                repSalesRouteSelect.innerHTML = '<option value="">All Routes</option>';
                
                // Populate dropdown with route data
                routes.forEach(route => {
                    let option = `<option value="${route.id}">${route.name}</option>`;
                    repSalesRouteSelect.innerHTML += option;
                });
            })
            .catch(error => console.error("Error fetching routes:", error));
    }
    
    // Function to load suppliers
    function loadSuppliers() {
        fetch("fetch_suppliers.php")
            .then(response => response.json())
            .then(suppliers => {
                let supplierSelect = document.getElementById("raw-stock-supplier");
                
                // Reset dropdown with default option
                supplierSelect.innerHTML = '<option value="">All Suppliers</option>';
                
                // Populate dropdown with supplier data
                suppliers.forEach(supplier => {
                    let option = `<option value="${supplier.id}">${supplier.name}</option>`;
                    supplierSelect.innerHTML += option;
                });
                
                // Also update branch dropdown
                loadBranchesForRawStock();
            })
            .catch(error => console.error("Error fetching suppliers:", error));
    }
    
    // Function to load branches for raw stock
    function loadBranchesForRawStock() {
        fetch("fetch_branch.php")
            .then(response => response.json())
            .then(branches => {
                let branchSelect = document.getElementById("raw-stock-branch");
                
                // Reset dropdown with default option
                branchSelect.innerHTML = '<option value="">All Branches</option>';
                
                // Populate dropdown with branch data
                branches.forEach(branch => {
                    let option = `<option value="${branch}">${branch}</option>`;
                    branchSelect.innerHTML += option;
                });
            })
            .catch(error => console.error("Error fetching branches for raw stock:", error));
    }
    
    // Raw Stock Report Functions
    function fetchRawStockReport() {
        let supplier = document.getElementById("raw-stock-supplier").value;
        let search = document.getElementById("raw-stock-search").value;
        let startDate = document.getElementById("raw-stock-start-date").value;
        let endDate = document.getElementById("raw-stock-end-date").value;
        let branch = document.getElementById("raw-stock-branch").value;
        
        let queryParams = new URLSearchParams({
            supplier: supplier,
            search: search,
            start_date: startDate,
            end_date: endDate,
            branch: branch
        });
        
        let url = `fetch_raw_stock_report.php?${queryParams.toString()}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                $("#reportTitle").text("Raw Stock Report");
                $("#reportModalLabel").text("Raw Stock Report");
                
                let tableHeaders = `
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Supplier</th>
                    <th>Purchase Qty</th>
                    <th>Unit</th>
                    <th>Cost Price</th>
                    <th>Total Cost</th>
                    <th>MRP</th>
                    <th>Expire Date</th>
                    <th>Branch</th>
                    <th>Date Added</th>
                `;
                
                let tableBody = "";
                let totalQuantity = 0;
                let totalCost = 0;
                
                if (!data.success || data.data.length === 0) {
                    tableBody = "<tr><td colspan='11'>No stock records found.</td></tr>";
                } else {
                    data.data.forEach(item => {
                        totalQuantity += parseInt(item.purchase_qty) || 0;
                        totalCost += parseFloat(item.total_cost_amount) || 0;
                        
                        tableBody += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.barcode || "N/A"}</td>
                            <td>${item.supplier_name || "N/A"}</td>
                            <td>${item.purchase_qty}</td>
                            <td>${item.unit || "N/A"}</td>
                            <td>${parseFloat(item.cost_price).toFixed(2)}</td>
                            <td>${parseFloat(item.total_cost_amount).toFixed(2)}</td>
                            <td>${parseFloat(item.max_retail_price).toFixed(2)}</td>
                            <td>${item.expire_date || "N/A"}</td>
                            <td>${item.branch}</td>
                            <td>${item.created_at}</td>
                        </tr>
                    `;
                    });
                    
                    // Append total row
                    tableBody += `
                    <tr style="font-weight: bold;">
                        <td colspan="3" style="text-align:right;">Total:</td>
                        <td>${totalQuantity}</td>
                        <td></td>
                        <td></td>
                        <td>${totalCost.toFixed(2)}</td>
                        <td colspan="4"></td>
                    </tr>
                `;
                }
                
                $("#reportTableHead").html(tableHeaders);
                $("#reportTableBody").html(tableBody);
                $("#reportModal").modal("show");
            })
            .catch(error => console.error("Error fetching raw stock report:", error));
    }
    
    function printRawStockReport() {
        let supplier = document.getElementById("raw-stock-supplier").value;
        let search = document.getElementById("raw-stock-search").value;
        let startDate = document.getElementById("raw-stock-start-date").value;
        let endDate = document.getElementById("raw-stock-end-date").value;
        let branch = document.getElementById("raw-stock-branch").value;
        
        let url = `print_raw_stock_report.php?supplier=${encodeURIComponent(supplier)}&search=${encodeURIComponent(search)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&branch=${encodeURIComponent(branch)}`;
        
        let printWindow = window.open(url, '_blank');
        
        if (printWindow) {
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }, 1000);
        } else {
            alert("Popup blocked! Allow popups for this site.");
        }
    }
    
    // Rep Payments Report Functions
    function fetchRepPaymentsReport() {
        let repId = document.getElementById("rep-payments-username").value;
        let startDate = document.getElementById("rep-payments-start-date").value;
        let endDate = document.getElementById("rep-payments-end-date").value;
        
        let queryParams = new URLSearchParams({
            rep_id: repId,
            start_date: startDate,
            end_date: endDate
        });
        
        let url = `fetch_rep_payments_report.php?${queryParams.toString()}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                $("#reportTitle").text("Rep Payments Report");
                $("#reportModalLabel").text("Rep Payments Report");
                
                let tableHeaders = `
                    <th>Invoice Number</th>
                    <th>Customer Name</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Cheque Number</th>
                    <th>Rep</th>
                    <th>Branch</th>
                    <th>Date</th>
                    <th>Notes</th>
                `;
                
                let tableBody = "";
                let totalAmount = 0;
                
                if (!data.success || data.data.length === 0) {
                    tableBody = "<tr><td colspan='9'>No payment records found.</td></tr>";
                } else {
                    data.data.forEach(item => {
                        totalAmount += parseFloat(item.amount) || 0;
                        
                        tableBody += `
                            <tr>
                                <td>${item.invoice_number}</td>
                                <td>${item.customer_name}</td>
                                <td>${parseFloat(item.amount).toFixed(2)}</td>
                                <td>${item.payment_method}</td>
                                <td>${item.cheque_num || "N/A"}</td>
                                <td>${item.rep_name}</td>
                                <td>${item.branch}</td>
                                <td>${item.payment_date}</td>
                                <td>${item.notes || ""}</td>
                            </tr>
                        `;
                    });
                    
                    // Append total row
                    tableBody += `
                        <tr style="font-weight: bold;">
                            <td colspan="2" style="text-align:right;">Total:</td>
                            <td>${totalAmount.toFixed(2)}</td>
                            <td colspan="6"></td>
                        </tr>
                    `;
                }
                
                $("#reportTableHead").html(tableHeaders);
                $("#reportTableBody").html(tableBody);
                $("#reportModal").modal("show");
            })
            .catch(error => console.error("Error fetching rep payments report:", error));
    }
    
    function printRepPaymentsReport() {
        let username = document.getElementById("rep-payments-username").value;
        let startDate = document.getElementById("rep-payments-start-date").value;
        let endDate = document.getElementById("rep-payments-end-date").value;
        
        let url = `print_rep_payments_report.php?username=${encodeURIComponent(username)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
        
        let printWindow = window.open(url, '_blank');
        
        if (printWindow) {
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }, 1000);
        } else {
            alert("Popup blocked! Allow popups for this site.");
        }
    }
    
    // Rep Sales Items Report Functions
    function fetchRepSalesReport() {
        let repId = document.getElementById("rep-sales-username").value;
        let startDate = document.getElementById("rep-sales-start-date").value;
        let endDate = document.getElementById("rep-sales-end-date").value;
        let barcode = document.getElementById("rep-sales-barcode").value;
        
        let queryParams = new URLSearchParams({
            rep_id: repId,
            start_date: startDate,
            end_date: endDate,
            barcode: barcode
        });
        
        let url = `fetch_rep_sales_report.php?${queryParams.toString()}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                $("#reportTitle").text("Rep Sales Items Report");
                $("#reportModalLabel").text("Rep Sales Items Report");
                
                let tableHeaders = `
                    <th>Invoice Number</th>
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Quantity</th>
                    <th>Free Quantity</th>
                    <th>Unit Price</th>
                    <th>Discount</th>
                    <th>Subtotal</th>
                    <th>Rep</th>
                    <th>Date</th>
                `;
                
                let tableBody = "";
                let totalQuantity = 0;
                let totalAmount = 0;
                
                if (!data.success || data.data.length === 0) {
                    tableBody = "<tr><td colspan='10'>No sales records found.</td></tr>";
                } else {
                    data.data.forEach(item => {
                        totalQuantity += parseInt(item.quantity) || 0;
                        totalAmount += parseFloat(item.subtotal) || 0;
                        
                        tableBody += `
                            <tr>
                                <td>${item.invoice_number}</td>
                                <td>${item.product_name}</td>
                                <td>${item.barcode || "N/A"}</td>
                                <td>${item.quantity}</td>
                                <td>${item.free_quantity}</td>
                                <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td>${parseFloat(item.discount_percent).toFixed(2)}%</td>
                                <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                                <td>${item.rep_name}</td>
                                <td>${item.sale_date}</td>
                            </tr>
                        `;
                    });
                    
                    // Append total row
                    tableBody += `
                        <tr style="font-weight: bold;">
                            <td colspan="3" style="text-align:right;">Total:</td>
                            <td>${totalQuantity}</td>
                            <td colspan="3"></td>
                            <td>${totalAmount.toFixed(2)}</td>
                            <td colspan="2"></td>
                        </tr>
                    `;
                }
                
                $("#reportTableHead").html(tableHeaders);
                $("#reportTableBody").html(tableBody);
                $("#reportModal").modal("show");
            })
            .catch(error => console.error("Error fetching rep sales report:", error));
    }
    
    function printRepSalesReport() {
        let username = document.getElementById("rep-sales-username").value;
        let route = document.getElementById("rep-sales-route").value;
        let startDate = document.getElementById("rep-sales-start-date").value;
        let endDate = document.getElementById("rep-sales-end-date").value;
        let barcode = document.getElementById("rep-sales-barcode").value;
        
        let url = `print_rep_sales_report.php?username=${encodeURIComponent(username)}&route=${encodeURIComponent(route)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&barcode=${encodeURIComponent(barcode)}`;
        
        let printWindow = window.open(url, '_blank');
        
        if (printWindow) {
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }, 1000);
        } else {
            alert("Popup blocked! Allow popups for this site.");
        }
    }
    
    // Rep Lorry Stock Report Functions
   // Rep Lorry Stock Report Functions
    function fetchRepLorryStockReport() {
        let repId = document.getElementById("rep-stock-username").value;
        let startDate = document.getElementById("rep-stock-start-date").value;
        let endDate = document.getElementById("rep-stock-end-date").value;
        let barcode = document.getElementById("rep-stock-barcode").value;
        
        let queryParams = new URLSearchParams({
            rep_id: repId,
            start_date: startDate,
            end_date: endDate,
            barcode: barcode
        });
        
        let url = `fetch_rep_lorry_stock_report.php?${queryParams.toString()}`;
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                $("#reportTitle").text("Rep Lorry Stock Report");
                $("#reportModalLabel").text("Rep Lorry Stock Report");
                
                let tableHeaders = `
                    <th>Product Name</th>
                    <th>Barcode</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total Value</th>
                    <th>Rep</th>
                    <th>Status</th>
                    <th>Last Updated</th>
                `;
                
                let tableBody = "";
                let totalQuantity = 0;
                let totalValue = 0;
                
                if (!data.success || data.data.length === 0) {
                    tableBody = "<tr><td colspan='8'>No lorry stock records found.</td></tr>";
                } else {
                    data.data.forEach(item => {
                        let itemValue = parseFloat(item.quantity) * parseFloat(item.unit_price);
                        totalQuantity += parseInt(item.quantity) || 0;
                        totalValue += itemValue;
                        
                        tableBody += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.barcode || "N/A"}</td>
                                <td>${item.quantity}</td>
                                <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td>${itemValue.toFixed(2)}</td>
                                <td>${item.rep_name}</td>
                                <td>${item.status}</td>
                                <td>${item.date_added}</td>
                            </tr>
                        `;
                    });
                    
                    // Append total row
                    tableBody += `
                        <tr style="font-weight: bold;">
                            <td colspan="2" style="text-align:right;">Total:</td>
                            <td>${totalQuantity}</td>
                            <td></td>
                            <td>${totalValue.toFixed(2)}</td>
                            <td colspan="3"></td>
                        </tr>
                    `;
                }
                
                $("#reportTableHead").html(tableHeaders);
                $("#reportTableBody").html(tableBody);
                $("#reportModal").modal("show");
            })
            .catch(error => console.error("Error fetching rep lorry stock report:", error));
    }
     
    function printRepLorryStockReport() {
        let username = document.getElementById("rep-stock-username").value;
        let startDate = document.getElementById("rep-stock-start-date").value;
        let endDate = document.getElementById("rep-stock-end-date").value;
        let barcode = document.getElementById("rep-stock-barcode").value;
        
        let url = `print_rep_lorry_stock_report.php?username=${encodeURIComponent(username)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}&barcode=${encodeURIComponent(barcode)}`;
        
        let printWindow = window.open(url, '_blank');
        
        if (printWindow) {
            setTimeout(() => {
                printWindow.focus();
                printWindow.print();
                printWindow.onafterprint = function() {
                    printWindow.close();
                };
            }, 1000);
        } else {
            alert("Popup blocked! Allow popups for this site.");
        }
    }

    // Function to fetch profit product report
function fetchProfitProductReport() {
    let category = document.getElementById("profit-category").value;
    let barcode = document.getElementById("profit-product-barcode").value;
    let startDate = document.getElementById("profitproduct-start-date").value;
    let endDate = document.getElementById("profitproduct-end-date").value;

    let queryParams = new URLSearchParams({
        category: category,
        barcode: barcode,
        start_date: startDate,
        end_date: endDate
    });

    let url = `fetch_profit_product_report.php?${queryParams.toString()}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Profit Report (Product Wise)");
            $("#reportModalLabel").text("Profit Report (Product Wise)");

            let tableHeaders = `
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Total Quantity Sold</th>
                <th>Cost Price</th>
                 
                <th>Total Revenue</th>
                <th>Total Cost</th>
                <th>Profit</th>
                <th>Profit %</th>
            `;
            
            let tableBody = "";
            let totalQuantitySold = 0;
            let totalRevenue = 0;
            let totalCost = 0;
            let totalProfit = 0;

            if (!data.success || data.data.length === 0) {
                tableBody = "<tr><td colspan='9'>No profit records found.</td></tr>";
            } else {
                data.data.forEach(item => {
                    let profit = parseFloat(item.total_revenue) - parseFloat(item.total_cost);
                    let profitPercentage = parseFloat(item.total_cost) > 0 ? 
                        ((profit / parseFloat(item.total_cost)) * 100).toFixed(2) + '%' : 'N/A';
                    
                    totalQuantitySold += parseInt(item.total_qty) || 0;
                    totalRevenue += parseFloat(item.total_revenue) || 0;
                    totalCost += parseFloat(item.total_cost) || 0;
                    totalProfit += profit;

                    tableBody += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.barcode || "N/A"}</td>
                            <td>${item.total_qty}</td>
                            <td>${parseFloat(item.cost_price).toFixed(2)}</td>
                             
                            <td>${parseFloat(item.total_revenue).toFixed(2)}</td>
                            <td>${parseFloat(item.total_cost).toFixed(2)}</td>
                            <td>${profit.toFixed(2)}</td>
                            <td>${profitPercentage}</td>
                        </tr>
                    `;
                });

                // Calculate total profit percentage
                let totalProfitPercentage = totalCost > 0 ? ((totalProfit / totalCost) * 100).toFixed(2) + '%' : 'N/A';

                // Append total row
                tableBody += `
                    <tr style="font-weight: bold;">
                        <td colspan="2" style="text-align:right;">Total:</td>
                        <td>${totalQuantitySold}</td>
                        <td>-</td>
                         
                        <td>${totalRevenue.toFixed(2)}</td>
                        <td>${totalCost.toFixed(2)}</td>
                        <td>${totalProfit.toFixed(2)}</td>
                        <td>${totalProfitPercentage}</td>
                    </tr>
                `;
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => console.error("Error fetching profit report:", error));
}

// Function to print profit product report
function printProfitProductReport() {
    let category = document.getElementById("profit-category").value;
    let barcode = document.getElementById("profit-product-barcode").value;
    let startDate = document.getElementById("profitproduct-start-date").value;
    let endDate = document.getElementById("profitproduct-end-date").value;

    let url = `print_profit_product_report.php?category=${encodeURIComponent(category)}&barcode=${encodeURIComponent(barcode)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        setTimeout(() => {
            printWindow.focus();
            printWindow.print();
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        }, 1000); // Delay to ensure full page load
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}



// Function to fetch rep sales profit product report
function fetchRepSalesProfitProductReport() {
    // let category = document.getElementById("profit-category").value;
    let barcode = document.getElementById("rep-profit-product-barcode").value;
    let startDate = document.getElementById("rep-profitproduct-start-date").value;
    let endDate = document.getElementById("rep-profitproduct-end-date").value;

    let queryParams = new URLSearchParams({
        // category: category,
        barcode: barcode,
        start_date: startDate,
        end_date: endDate
    });

    let url = `fetch_rep_sales_profit_product_report.php?${queryParams.toString()}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Rep Sales Profit Report (Product Wise)");
            $("#reportModalLabel").text("Rep Sales Profit Report (Product Wise)");

            let tableHeaders = `
                <th>Product Name</th>
                <th>Barcode</th>
                <th>Total Quantity Sold</th>
                <th>Cost Price</th>
                
                <th>Total Revenue</th>
                <th>Total Cost</th>
                <th>Profit</th>
                <th>Profit %</th>
            `;
            
            let tableBody = "";
            let totalQuantitySold = 0;
            let totalRevenue = 0;
            let totalCost = 0;
            let totalProfit = 0;

            if (!data.success || data.data.length === 0) {
                tableBody = "<tr><td colspan='9'>No profit records found.</td></tr>";
            } else {
                data.data.forEach(item => {
                    let profit = parseFloat(item.total_revenue) - parseFloat(item.total_cost);
                    let profitPercentage = parseFloat(item.total_cost) > 0 ? 
                        ((profit / parseFloat(item.total_cost)) * 100).toFixed(2) + '%' : 'N/A';
                    
                    totalQuantitySold += parseInt(item.total_qty) || 0;
                    totalRevenue += parseFloat(item.total_revenue) || 0;
                    totalCost += parseFloat(item.total_cost) || 0;
                    totalProfit += profit;

                    tableBody += `
                        <tr>
                            <td>${item.product_name}</td>
                            <td>${item.barcode || "N/A"}</td>
                            <td>${item.total_qty}</td>
                            <td>${parseFloat(item.cost_price).toFixed(2)}</td>
                            
                            <td>${parseFloat(item.total_revenue).toFixed(2)}</td>
                            <td>${parseFloat(item.total_cost).toFixed(2)}</td>
                            <td>${profit.toFixed(2)}</td>
                            <td>${profitPercentage}</td>
                        </tr>
                    `;
                });

                // Calculate total profit percentage
                let totalProfitPercentage = totalCost > 0 ? ((totalProfit / totalCost) * 100).toFixed(2) + '%' : 'N/A';

                // Append total row
                tableBody += `
                    <tr style="font-weight: bold;">
                        <td colspan="2" style="text-align:right;">Total:</td>
                        <td>${totalQuantitySold}</td>
                        <td>-</td>
                        
                        <td>${totalRevenue.toFixed(2)}</td>
                        <td>${totalCost.toFixed(2)}</td>
                        <td>${totalProfit.toFixed(2)}</td>
                        <td>${totalProfitPercentage}</td>
                    </tr>
                `;
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => console.error("Error fetching profit report:", error));
}

// Function to print rep sales profit product report
function printRepSalesProfitProductReport() {
    //let category = document.getElementById("profit-category").value;
    let barcode = document.getElementById("rep-profit-product-barcode").value;
    let startDate = document.getElementById("rep-profitproduct-start-date").value;
    let endDate = document.getElementById("rep-profitproduct-end-date").value;

    let url = `print_rep_sales_profit_product_report.php?barcode=${encodeURIComponent(barcode)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        setTimeout(() => {
            printWindow.focus();
            printWindow.print();
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        }, 1000); // Delay to ensure full page load
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}
</script>

</html>