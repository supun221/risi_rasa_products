<?php
// Rep Sales Items Report Section
require_once '../../../../config/databade.php';

// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

// Default date ranges
$today = date('Y-m-d');
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

// Fetch all reps for dropdown
$all_reps = [];
try {
    $stmt = $conn->prepare("SELECT id, username FROM signup ORDER BY username");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $all_reps[] = $row;
    }
} catch (Exception $e) {
    // Error handling
}

// Fetch all routes for dropdown
$all_routes = [];
try {
    $stmt = $conn->prepare("SELECT id, name FROM routes ORDER BY name");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $all_routes[] = $row;
    }
} catch (Exception $e) {
    // Error handling
}

// Fetch all customers for dropdown
$all_customers = [];
try {
    $stmt = $conn->prepare("SELECT id, name FROM customers ORDER BY name LIMIT 500");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $all_customers[] = $row;
    }
} catch (Exception $e) {
    // Error handling
}
?>

<div class="section-card fade-transition" id="rep-sales-report-section">
    <div class="section-header">
        <i class="fas fa-file-invoice"></i> Rep Sales Items Report
    </div>

    <div class="section-body">
        <a href="#" class="return-link" id="return-from-rep-sales-report">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <a href="#" class="return-link ml-3" id="back-to-reports">
            <i class="fas fa-chevron-left"></i> Back to Reports
        </a>

        <div class="card mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Report Filters</h5>
            </div>
            <div class="card-body">
                <form id="rep-sales-report-form">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="rep-sales-username">Select Rep:</label>
                            <select id="rep-sales-username" class="form-control">
                                <option value="">All Reps</option>
                                <?php foreach($all_reps as $rep): ?>
                                <option value="<?php echo $rep['id']; ?>" <?php echo $rep['id'] == $rep_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($rep['username']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3 form-group">
                            <label for="rep-sales-route">Select Route:</label>
                            <select id="rep-sales-route" class="form-control">
                                <option value="">All Routes</option>
                                <?php foreach($all_routes as $route): ?>
                                <option value="<?php echo $route['id']; ?>">
                                    <?php echo htmlspecialchars($route['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3 form-group">
                            <label for="rep-sales-customer">Select Customer:</label>
                            <select id="rep-sales-customer" class="form-control">
                                <option value="">All Customers</option>
                                <?php foreach($all_customers as $customer): ?>
                                <option value="<?php echo $customer['id']; ?>">
                                    <?php echo htmlspecialchars($customer['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="rep-sales-start-date">Start Date:</label>
                            <input type="date" id="rep-sales-start-date" class="form-control" value="<?php echo $month_start; ?>">
                        </div>
                        
                        <div class="col-md-3 form-group">
                            <label for="rep-sales-end-date">End Date:</label>
                            <input type="date" id="rep-sales-end-date" class="form-control" value="<?php echo $today; ?>">
                        </div>
                        
                        <div class="col-md-3 form-group">
                            <label for="rep-sales-barcode">Barcode/Item Code:</label>
                            <input type="text" id="rep-sales-barcode" class="form-control" placeholder="Search by Barcode">
                        </div>
                        
                        <div class="col-md-3 form-group d-flex align-items-end">
                            <button type="button" class="btn btn-primary mr-2" id="run-rep-sales-report">
                                <i class="fas fa-search mr-1"></i> Run Report
                            </button>
                            <button type="button" class="btn btn-success" id="print-rep-sales-report">
                                <i class="fas fa-print mr-1"></i> Print Report
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Report Results</h5>
                <button type="button" class="btn btn-sm btn-outline-success" id="export-excel-report">
                    <i class="fas fa-file-excel mr-1"></i> Export to Excel
                </button>
            </div>
            <div class="card-body p-0">
                <div id="rep-sales-report-results">
                    <!-- Report results will be loaded here -->
                    <div class="text-center p-5">
                        <p class="text-muted">Use the filters above to run the report.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Return to dashboard link
    $('#return-from-rep-sales-report').click(function(e) {
        e.preventDefault();
        $('.section-card').hide();
        $('#dashboard-section').show();
    });

    // Add back button that returns to the reports section
    $('#back-to-reports').click(function(e) {
        e.preventDefault();
        $('.section-card').hide();
        $('#tile-report-section').show();
    });

    // Run report
    $('#run-rep-sales-report').click(function() {
        fetchRepSalesReport();
    });

    // Print report
    $('#print-rep-sales-report').click(function() {
        printRepSalesReport();
    });

    // Export to Excel
    $('#export-excel-report').click(function() {
        exportRepSalesReportToExcel();
    });

    // Initialize select2 for better dropdown experience
    if ($.fn.select2) {
        $('#rep-sales-customer').select2({
            placeholder: 'Select a customer',
            allowClear: true
        });
    }
});

// Function to fetch report data
function fetchRepSalesReport() {
    const repId = $('#rep-sales-username').val();
    const routeId = $('#rep-sales-route').val();
    const customerId = $('#rep-sales-customer').val();
    const startDate = $('#rep-sales-start-date').val();
    const endDate = $('#rep-sales-end-date').val();
    const barcode = $('#rep-sales-barcode').val();
    
    // Show loading indicator
    $('#rep-sales-report-results').html(
        '<div class="text-center p-5">' +
        '<div class="spinner-border text-primary" role="status">' +
        '<span class="sr-only">Loading...</span>' +
        '</div>' +
        '<p class="mt-3">Loading report data...</p>' +
        '</div>'
    );
    
    // Fetch report data via AJAX
    $.ajax({
        url: 'process/fetch_rep_sales_report.php',
        type: 'GET',
        data: {
            rep_id: repId,
            route_id: routeId,
            customer_id: customerId,
            start_date: startDate,
            end_date: endDate,
            barcode: barcode
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                displayRepSalesReport(response.data);
            } else {
                $('#rep-sales-report-results').html(
                    '<div class="alert alert-danger m-3">' +
                    '<i class="fas fa-exclamation-circle mr-2"></i>' +
                    'Error: ' + response.message +
                    '</div>'
                );
            }
        },
        error: function(xhr, status, error) {
            $('#rep-sales-report-results').html(
                '<div class="alert alert-danger m-3">' +
                '<i class="fas fa-exclamation-circle mr-2"></i>' +
                'Error loading report. Please try again.' +
                '</div>'
            );
        }
    });
}

// Function to display report data
function displayRepSalesReport(data) {
    if (data.length === 0) {
        $('#rep-sales-report-results').html(
            '<div class="alert alert-info m-3">' +
            '<i class="fas fa-info-circle mr-2"></i>' +
            'No data found for the selected criteria.' +
            '</div>'
        );
        return;
    }
    
    // Calculate totals
    let totalQuantity = 0;
    let totalSubtotal = 0;
    
    // Group by invoice for better organization
    const invoiceGroups = {};
    
    data.forEach(item => {
        if (!invoiceGroups[item.invoice_number]) {
            invoiceGroups[item.invoice_number] = {
                invoice_number: item.invoice_number,
                sale_date: item.sale_date,
                customer_name: item.customer_name,
                route_name: item.route_name,
                rep_name: item.rep_name,
                items: []
            };
        }
        
        invoiceGroups[item.invoice_number].items.push(item);
        
        totalQuantity += parseFloat(item.quantity);
        totalSubtotal += parseFloat(item.subtotal);
    });
    
    // Create HTML output
    let html = `
        <div class="table-responsive">
            <table class="table table-sm table-hover">
                <thead>
                    <tr>
                        <th>Invoice #</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Route</th>
                        <th>Rep</th>
                        <th>Product</th>
                        <th>Barcode</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Loop through invoices and items
    Object.keys(invoiceGroups).forEach(invoiceNumber => {
        const invoice = invoiceGroups[invoiceNumber];
        const invoiceDate = new Date(invoice.sale_date).toLocaleDateString();
        
        let isFirst = true;
        
        invoice.items.forEach(item => {
            html += '<tr>';
            
            // Only show invoice details on the first item row
            if (isFirst) {
                html += `
                    <td>${invoice.invoice_number}</td>
                    <td>${invoiceDate}</td>
                    <td>${item.customer_name || 'Walk-in Customer'}</td>
                    <td>${item.route_name || 'N/A'}</td>
                    <td>${item.rep_name || 'N/A'}</td>
                `;
                isFirst = false;
            } else {
                html += '<td colspan="5"></td>';
            }
            
            // Item details
            html += `
                <td>${item.product_name}</td>
                <td>${item.barcode || 'N/A'}</td>
                <td class="text-right">${parseFloat(item.quantity).toFixed(0)}</td>
                <td class="text-right">${parseFloat(item.unit_price).toFixed(2)}</td>
                <td class="text-right">${parseFloat(item.discount_amount).toFixed(2)}</td>
                <td class="text-right">${parseFloat(item.subtotal).toFixed(2)}</td>
            `;
            
            html += '</tr>';
        });
        
        // Add a separator row between invoices
        html += '<tr><td colspan="11" class="border-bottom"></td></tr>';
    });
    
    html += `
                </tbody>
                <tfoot>
                    <tr class="font-weight-bold">
                        <td colspan="7" class="text-right">Total:</td>
                        <td class="text-right">${totalQuantity.toFixed(0)}</td>
                        <td></td>
                        <td></td>
                        <td class="text-right">${totalSubtotal.toFixed(2)}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    $('#rep-sales-report-results').html(html);
}

// Function to print report
function printRepSalesReport() {
    const repId = $('#rep-sales-username').val();
    const routeId = $('#rep-sales-route').val();
    const customerId = $('#rep-sales-customer').val();
    const startDate = $('#rep-sales-start-date').val();
    const endDate = $('#rep-sales-end-date').val();
    const barcode = $('#rep-sales-barcode').val();
    
    // Build query string for print page
    const queryString = `?rep_id=${repId}&route_id=${routeId}&customer_id=${customerId}&start_date=${startDate}&end_date=${endDate}&barcode=${barcode}`;
    
    // Open print window
    window.open('process/print_rep_sales_report.php' + queryString, '_blank');
}

// Function to export report to Excel
function exportRepSalesReportToExcel() {
    const repId = $('#rep-sales-username').val();
    const routeId = $('#rep-sales-route').val();
    const customerId = $('#rep-sales-customer').val();
    const startDate = $('#rep-sales-start-date').val();
    const endDate = $('#rep-sales-end-date').val();
    const barcode = $('#rep-sales-barcode').val();
    
    // Build query string for export
    const queryString = `?rep_id=${repId}&route_id=${routeId}&customer_id=${customerId}&start_date=${startDate}&end_date=${endDate}&barcode=${barcode}&export=excel`;
    
    // Redirect to export script
    window.location.href = 'process/export_rep_sales_report.php' + queryString;
}
</script>

<style>
#rep-sales-report-section .card {
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    border: none;
    border-radius: 10px;
}

#rep-sales-report-section .card-header {
    border-bottom: 1px solid #eee;
    padding: 15px 20px;
}

#rep-sales-report-section .table th {
    font-weight: 600;
    background-color: #f8f9fa;
}

#rep-sales-report-section .table td, 
#rep-sales-report-section .table th {
    vertical-align: middle;
    padding: 8px;
}

#rep-sales-report-results .table tr:hover {
    background-color: #f8f9fa;
}
</style>
