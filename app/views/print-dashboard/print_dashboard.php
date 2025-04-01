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
    <title>Eggland | Report Dashboard</title>
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
    <span class="report-page-header">Eggland Super Report Export Page</span>

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
            </script>

            <script>
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
<!-- <script>
    function fetchCategories() {
        fetch("../dashboard/get_categories.php")
            .then(response => response.json())
            .then(data => {
                let categorySelect = document.getElementById("category");
                let lowCategorySelect = document.getElementById("low-category");
                categorySelect.innerHTML = '<option value="">Select Category</option>';
                lowCategorySelect.innerHTML = '<option value="">All Category</option>';
                data.forEach(category => {
                    let option = `<option value="${category}">${category}</option>`;
                    categorySelect.innerHTML += option;
                    lowCategorySelect.innerHTML += option;
                });
            })
            .catch(error => console.error("Error fetching categories:", error));
    }
</script> -->
<script>
    document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });
</script>

</html>