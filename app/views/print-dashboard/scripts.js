//scrpit.js
document.addEventListener("DOMContentLoaded", function () {
    fetchSuppliers();
    fetchCategories();
    loadUsers();
});

function fetchSuppliers() {
    fetch("../inventory/get_suppliers.php")
        .then(response => response.json())
        .then(data => {
            let supplierSelect = document.getElementById("supplier");
            supplierSelect.innerHTML = '<option value="">All Supplier</option>';
            data.forEach(supplier => {
                supplierSelect.innerHTML += `<option value="${supplier.supplier_id}">${supplier.supplier_name}</option>`;
            });
        })
        .catch(error => console.error("Error fetching suppliers:", error));
}

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
function fetchDeleteBillReport() {
    let user = document.getElementById("users-select-delete-bills").value.trim();
    let startDate = document.getElementById("deletebill-start-date").value;
    let endDate = document.getElementById("deletebill-end-date").value;

    let url = `fetch_deleted_bills.php?user=${encodeURIComponent(user)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Deleted Bills Report");
            $("#reportModalLabel").text("Deleted Bills Report");

            let tableHeaders = `
                <th>Bill ID</th>
                <th>Cancelled By</th>
                <th>Cancelled Date</th>
                <th>Reason</th>
                <th>Bill Amount</th>
            `;
            let tableBody = "";

            if (!data.success || data.data.length === 0) {
                tableBody = "<tr><td colspan='5'>No deleted bills found.</td></tr>";
            } else {
                data.data.forEach(bill => {
                    tableBody += `
                        <tr>
                            <td>${bill.bill_id}</td>
                            <td>${bill.cancelled_by}</td>
                            <td>${bill.cancelled_date}</td>
                            <td>${bill.reason}</td>
                            <td>${bill.bill_amount}</td>
                        </tr>
                    `;
                });
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => console.error("Error fetching deleted bills:", error));
}


function fetchTotalStockReport() {
    let supplier = document.getElementById("supplier").value;
    let category = document.getElementById("category").value;
    let searchQuery = document.getElementById("search").value;
    let startDate = document.getElementById("start-date").value;
    let endDate = document.getElementById("end-date").value;

    let url = `../dashboard/fetch_total_stock.php?supplier=${encodeURIComponent(supplier)}&category=${encodeURIComponent(category)}&search=${encodeURIComponent(searchQuery)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Total Stock Report");
            $("#reportModalLabel").text("Total Stock Report");

            let tableHeaders = `
          <th>Product Name</th>
          <th>Available Stock</th>
          <th>Cost Price</th>
          <th>Retail Price</th>
          <th>Barcode</th>
          <th>Supplier</th>
        `;
            let tableBody = "";

            if (data.length === 0) {
                tableBody = "<tr><td colspan='6'>No stock items found.</td></tr>";
            } else {
                data.forEach(item => {
                    tableBody += `
              <tr>
                <td>${item.product_name}</td>
                <td>${item.available_stock}</td>
                <td>${item.cost_price}</td>
                <td>${item.max_retail_price}</td>
                <td>${item.barcode}</td>
                <td>${item.supplier_name}</td>
              </tr>
            `;
                });
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => console.error("Error fetching data:", error));
}


function fetchLowStockReport() {
    let category = document.getElementById("low-category").value;
    let searchQuery = document.getElementById("search-low").value;

    let url = `../dashboard/low_stock_ajax.php?category=${encodeURIComponent(category)}&search=${encodeURIComponent(searchQuery)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Low Stock Report");
            $("#reportModalLabel").text("Low Stock Report");

            let tableHeaders = `
          <th>Product Name</th>
          <th>Low Stock</th>
          <th>Available Stock</th>
          <th>Barcode</th>
          <th>Supplier</th>
        `;
            let tableBody = "";

            if (data.length === 0) {
                tableBody = "<tr><td colspan='5'>No low stock items found.</td></tr>";
            } else {
                data.forEach(item => {
                    tableBody += `
              <tr>
                <td>${item.product_name}</td>
                <td>${item.low_stock}</td>
                <td>${item.available_stock}</td>
                <td>${item.barcode}</td>
                <td>${item.supplier_name}</td>
              </tr>
            `;
                });
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => console.error("Error fetching data:", error));
}



function fetchOutstandingReport() {
    let startDate = document.getElementById("outstanding-start-date").value;
    let endDate = document.getElementById("outstanding-end-date").value;

    if (!startDate || !endDate) {
        alert("Please select a start and end date.");
        return;
    }

    let url = `fetch_outstanding_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            console.log("Outstanding Report Data:", data); // Debugging

            $("#reportTitle").text("Outstanding Report");
            $("#reportModalLabel").text("Outstanding Report");

            let tableHeaders = `
                <th>Supplier Name</th>
                <th>Phone</th>
                <th>Company</th>
                <th>Credit Balance</th>
                <th>Created Date</th>
            `;

            let tableBody = "";
            let totalOutstanding = 0;

            if (data && data.records && data.records.length > 0) {
                tableBody = data.records.map(item => {
                    let creditBalance = parseFloat(item.credit_balance) || 0;
                    totalOutstanding += creditBalance; // Sum up outstanding balance

                    return `
                        <tr>
                            <td>${item.supplier_name}</td>
                            <td>${item.telephone_no}</td>
                            <td>${item.company}</td>
                            <td>${creditBalance.toFixed(2)}</td>
                            <td>${item.created_at}</td>
                        </tr>
                    `;
                }).join('');
            } else {
                tableBody = "<tr><td colspan='5'>No outstanding balances found.</td></tr>";
            }

            // Append Total Outstanding row
            tableBody += `
                <tr style="font-weight: bold; background-color: #f8d775;">
                    <td colspan="3">Total Outstanding</td>
                    <td colspan="2">${totalOutstanding.toFixed(2)}</td>
                </tr>
            `;

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => {
            console.error("Error fetching outstanding report data:", error);
            $("#reportTableBody").html("<tr><td colspan='5'>Error fetching data</td></tr>");
        });
}




function fetchExpireReport() {
    let user = document.getElementById("expire-user") ? document.getElementById("expire-user").value : "";
    let startDate = document.getElementById("expire-start-date").value;
    let endDate = document.getElementById("expire-end-date").value;

    // if (!startDate || !endDate) {
    //     alert("Please select a start and end date.");
    //     return;
    // }

    let url = `../dashboard/expire_alerts.php?user=${encodeURIComponent(user)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (!Array.isArray(data) || data.length === 0) {
                $("#reportTableBody").html("<tr><td colspan='6'>No expired items found in the selected date range.</td></tr>");
                $("#reportModal").modal("show");
                return;
            }

            $("#reportTitle").text("Expire Report");
            $("#reportModalLabel").text("Expire Report");

            let tableHeaders = `
                <th>Barcode</th>
                <th>Product Name</th>
                <th>Available Quantity</th>
                <th>Supplier</th>
                <th>Expire Date</th>
            `;

            let tableBody = "";
            let totalExpiredItems = 0;
            let totalExpiredQuantity = 0;

            data.forEach(item => {
                let itemExpireDate = new Date(item.expire_date);
                let selectedStartDate = new Date(startDate);
                let selectedEndDate = new Date(endDate);

                // Ensure only items that expired within the selected date range are shown
                if (itemExpireDate >= selectedStartDate && itemExpireDate <= selectedEndDate) {
                    totalExpiredItems++;
                    totalExpiredQuantity += parseInt(item.available_stock, 10);

                    tableBody += `
                        <tr>
                            <td>${item.barcode}</td>
                            <td>${item.product_name}</td>
                            <td>${item.available_stock}</td>
                            <td>${item.supplier_name}</td>
                            <td>${item.expire_date}</td>
                        </tr>
                    `;
                }
            });

            // Show message if no items match the filter
            if (totalExpiredItems === 0) {
                tableBody = "<tr><td colspan='6'>No expired items found in the selected date range.</td></tr>";
            } else {
                // Append summary row
                tableBody += `
                    <tr style="font-weight: bold; background-color: #ffeb99;">
                        <td colspan="2">Total Expired Items</td>
                        <td colspan="2">${totalExpiredItems}</td>
                        <td colspan="2">${totalExpiredQuantity}</td>
                    </tr>
                `;
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => {
            console.error("Error fetching expire report:", error);
            alert("Failed to fetch Expire Report. Please try again.");
        });
}

function fetchReturnReport() {
    let user = document.getElementById("return-user") ? document.getElementById("return-user").value : "";
    let startDate = document.getElementById("return-start-date").value;
    let endDate = document.getElementById("return-end-date").value;

    // if (!startDate || !endDate) {
    //     alert("Please select a start and end date.");
    //     return;
    // }

    let url = `../dashboard/fetch_return_ajax.php?user=${encodeURIComponent(user)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Return Report");
            $("#reportModalLabel").text("Return Report");

            let tableHeaders = `
                <th>Return ID</th>
                <th>Supplier</th>
                <th>Return Date</th>
                <th>Total Amount</th>
              
            `;

            let tableBody = data.length ? data.map(item => `
                <tr>
                    <td>${item.id}</td>
                    <td>${item.supplier_name}</td>
                    <td>${item.return_date}</td>
                    <td>${item.total_amount}</td>
               
                </tr>
            `).join('') : "<tr><td colspan='5'>No return items found.</td></tr>";

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => {
            console.error("Error fetching return report:", error);
            alert("Failed to fetch Return Report. Please try again.");
        });
}


function fetchDeleteBillItemsReport() {
    let user = document.getElementById("users-select-delete-items").value.trim();
    let startDate = document.getElementById("delete-start-date").value;
    let endDate = document.getElementById("delete-end-date").value;

    let url = `fetch_deleted_bill_items.php?user=${encodeURIComponent(user)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Deleted Bill Items Report");
            $("#reportModalLabel").text("Deleted Bill Items Report");

            let tableHeaders = `
<th>Bill ID</th>
<th>Deleted By</th>
<th>Deleted Date</th>
<th>Bill Type</th>
<th>Products</th>
<th>Barcodes</th>
<th>Prices</th>
<th>Quantities</th>
<th>Discounts</th>
<th>Totals</th>
`;
            let tableBody = "";

            if (!data.success || data.data.length === 0) {
                tableBody = "<tr><td colspan='10'>No deleted bill items found.</td></tr>";
            } else {
                let bills = {};

                // Group items by bill_id
                data.data.forEach(item => {
                    if (!bills[item.bill_id]) {
                        bills[item.bill_id] = {
                            bill_id: item.bill_id,
                            deleted_by: item.deleted_by,
                            deleted_date: item.deleted_date,
                            bill_type: item.bill_type,
                            purchase_items: []
                        };
                    }

                    bills[item.bill_id].purchase_items.push({
                        item_name: item.item_name,
                        barcode: item.barcode,
                        unit_price: item.unit_price,
                        quantity: item.quantity,
                        discount: item.discount,
                        total_price: item.total_price
                    });
                });

                // Generate table rows
                Object.values(bills).forEach(bill => {
                    let productDetails = bill.purchase_items.map(item => item.item_name).join("<br>");
                    let barcodeDetails = bill.purchase_items.map(item => item.barcode).join("<br>");
                    let priceDetails = bill.purchase_items.map(item => Number(item.unit_price).toFixed(2)).join("<br>");
                    let qtyDetails = bill.purchase_items.map(item => item.quantity).join("<br>");
                    let discountDetails = bill.purchase_items.map(item => Number(item.discount).toFixed(2)).join("<br>");
                    let totalDetails = bill.purchase_items.map(item => Number(item.total_price).toFixed(2)).join("<br>");

                    tableBody += `
        <tr>
            <td>${bill.bill_id}</td>
            <td>${bill.deleted_by}</td>
            <td>${bill.deleted_date}</td>
            <td>${bill.bill_type}</td>
            <td>${productDetails}</td>
            <td>${barcodeDetails}</td>
            <td>${priceDetails}</td>
            <td>${qtyDetails}</td>
            <td>${discountDetails}</td>
            <td>${totalDetails}</td>
        </tr>
    `;
                });
            }

            $("#reportTableHead").html(tableHeaders);
            $("#reportTableBody").html(tableBody);
            $("#reportModal").modal("show");
        })
        .catch(error => console.error("Error fetching deleted bill items:", error));
}





function fetchSalesReport() {
    let startDate = document.getElementById("sales-start-date").value;
    let endDate = document.getElementById("sales-end-date").value;

    let url = `fetch_sales_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            $("#reportTitle").text("Sales Report (Invoice Wise)");
            $("#reportModalLabel").text("Sales Report (Invoice Wise)");

            let tableHeaders = `
<th>Bill ID</th>
<th>Customer ID</th>
<th>Bill Date</th>
<th>Products</th>
<th>Price</th>
<th>Qty</th>
<th>Discount %</th>
<th>Subtotal</th>
<th>Gross Amount</th>
<th>Net Amount</th>
<th>Discount</th>
`;
            let tableBody = "";

            if (!data.success || data.data.length === 0) {
                tableBody = "<tr><td colspan='11'>No sales records found.</td></tr>";
            } else {
                data.data.forEach(bill => {
                    let productDetails = bill.purchase_items.map(item => `${item.product_name}`).join("<br>");
                    let priceDetails = bill.purchase_items.map(item => `${item.price}`).join("<br>");
                    let qtyDetails = bill.purchase_items.map(item => `${item.purchase_qty}`).join("<br>");
                    let discountDetails = bill.purchase_items.map(item => `${item.discount_percentage}%`).join("<br>");
                    let subtotalDetails = bill.purchase_items.map(item => `${item.subtotal}`).join("<br>");

                    tableBody += `
        <tr>
            <td>${bill.bill_id}</td>
            <td>${bill.customer_id}</td>
            <td>${bill.bill_date}</td>
            <td>${productDetails}</td>
            <td>${priceDetails}</td>
            <td>${qtyDetails}</td>
            <td>${discountDetails}</td>
            <td>${subtotalDetails}</td>
            <td>${bill.gross_amount}</td>
            <td>${bill.net_amount}</td>
            <td>${bill.discount_amount}</td>
        </tr>
    `;
                });
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

    let url = `print_sales_product_report.php?category=${encodeURIComponent(category)}&barcode=${encodeURIComponent(barcode)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;

    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            // Automatically trigger the print dialog
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printDeleteBillReport() {
    let user = document.getElementById("users-select-delete-bills").value.trim();
    let startDate = document.getElementById("deletebill-start-date").value;
    let endDate = document.getElementById("deletebill-end-date").value;

    let url = `print_deleted_bills.php?user=${encodeURIComponent(user)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printTotalStockReport() {
    let supplier = document.getElementById("supplier").value;
    let category = document.getElementById("category").value;
    let searchQuery = document.getElementById("search").value;
    let startDate = document.getElementById("start-date").value;
    let endDate = document.getElementById("end-date").value;

    let url = `print_total_stock.php?supplier=${encodeURIComponent(supplier)}&category=${encodeURIComponent(category)}&search=${encodeURIComponent(searchQuery)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printLowStockReport() {
    let category = document.getElementById("low-category").value;
    let url = "print_low_stock.php?category=" + encodeURIComponent(category);
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printOutstandingReport() {
    let startDate = document.getElementById("outstanding-start-date").value;
    let endDate = document.getElementById("outstanding-end-date").value;

    let url = `print_outstanding_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printExpireReport() {
    let startDate = document.getElementById("expire-start-date").value;
    let endDate = document.getElementById("expire-end-date").value;

    let url = `print_expire_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printReturnReport() {
    let startDate = document.getElementById("return-start-date").value;
    let endDate = document.getElementById("return-end-date").value;

    let url = `print_return_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printDeleteBillItemsReport() {
    let user = document.getElementById("users-select-delete-items").value;
    let startDate = document.getElementById("delete-start-date").value;
    let endDate = document.getElementById("delete-end-date").value;

    let url = `print_deleted_bill_items.php?user=${encodeURIComponent(user)}&start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}

function printSalesReport() {
    let startDate = document.getElementById("sales-start-date").value;
    let endDate = document.getElementById("sales-end-date").value;

    let url = `print_sales_report.php?start_date=${encodeURIComponent(startDate)}&end_date=${encodeURIComponent(endDate)}`;
    let printWindow = window.open(url, '_blank');

    if (printWindow) {
        printWindow.onload = function () {
            printWindow.focus();
            printWindow.print();
        };
    } else {
        alert("Popup blocked! Allow popups for this site.");
    }
}