<?php
// History section for transaction history
require_once '../../../../config/databade.php';
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Default to sales history tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'sales';

// Get recent sales (limit to 10 most recent)
$sales = [];
try {
    $stmt = $conn->prepare("
        SELECT ps.id, ps.invoice_number, ps.customer_name, ps.net_amount, 
               ps.payment_method, ps.sale_date, COUNT(psi.id) as items_count
        FROM pos_sales ps
        LEFT JOIN pos_sale_items psi ON ps.id = psi.sale_id
        WHERE ps.rep_id = ?
        GROUP BY ps.id
        ORDER BY ps.sale_date DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $sales[] = $row;
    }
} catch (Exception $e) {
    $sales_error = $e->getMessage();
}

// Get recent stock movements (additions and retrievals)
$transactions = [];
try {
    $stmt = $conn->prepare("
        SELECT lt.id, lt.transaction_type, lt.quantity, lt.reason,
               lt.customer_name, lt.price, lt.total_amount, lt.transaction_date,
               lt.barcode, se.product_name, se.itemcode
        FROM lorry_transactions lt
        LEFT JOIN stock_entries se ON lt.stock_entry_id = se.id
        WHERE lt.rep_id = ?
        ORDER BY lt.transaction_date DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
} catch (Exception $e) {
    $transactions_error = $e->getMessage();
}

// Get payment history
$payments = [];
try {
    $stmt = $conn->prepare("
        SELECT rp.id, rp.invoice_number, rp.customer_name, rp.amount, 
               rp.payment_method, rp.payment_date, rp.notes
        FROM rep_payments rp
        WHERE rp.rep_id = ?
        ORDER BY rp.payment_date DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $rep_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $payments[] = $row;
    }
} catch (Exception $e) {
    $payments_error = $e->getMessage();
}

// Get advance payment history
$advance_payments = [];
try {
    $stmt = $conn->prepare("
        SELECT ap.id, ap.advance_bill_number, ap.customer_name, ap.net_amount, 
               ap.payment_type, ap.created_at, c.advance_amount as current_balance
        FROM advance_payments ap
        LEFT JOIN customers c ON ap.customer_id = c.id
        ORDER BY ap.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $advance_payments[] = $row;
    }
} catch (Exception $e) {
    $advance_payments_error = $e->getMessage();
}
?>

<div class="section-card fade-transition" id="history-section">
    <div class="section-header">
        <i class="fas fa-history"></i> Transaction History
    </div>
    <div class="section-body">
        <a href="#" class="return-link" id="return-from-history">
            <i class="fas fa-chevron-left"></i> Return to Dashboard
        </a>
        
        <!-- History Navigation Tabs -->
        <ul class="nav nav-tabs mb-4" id="historyTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab == 'sales') ? 'active' : ''; ?>" id="sales-tab" data-toggle="tab" href="#sales" role="tab">
                    <i class="fas fa-shopping-cart"></i> Sales History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab == 'stock') ? 'active' : ''; ?>" id="stock-tab" data-toggle="tab" href="#stock" role="tab">
                    <i class="fas fa-exchange-alt"></i> Stock Movements
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab == 'payments') ? 'active' : ''; ?>" id="payments-tab" data-toggle="tab" href="#payments" role="tab">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($active_tab == 'advance') ? 'active' : ''; ?>" id="advance-tab" data-toggle="tab" href="#advance" role="tab">
                    <i class="fas fa-hand-holding-usd"></i> Advance
                </a>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="historyTabContent">
            <!-- Sales History Tab -->
            <div class="tab-pane fade <?php echo ($active_tab == 'sales') ? 'show active' : ''; ?>" id="sales" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Sales History</h5>
                        <div class="form-inline">
                            <input type="text" class="form-control form-control-sm mr-2" id="sales-search" placeholder="Search sales...">
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-sales">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="sales-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Items</th>
                                        <th>Amount</th>
                                        <th>Payment</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sales)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No sales records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sales as $sale): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($sale['invoice_number']); ?></td>
                                                <td><?php echo htmlspecialchars($sale['customer_name'] ?: 'Walk-in Customer'); ?></td>
                                                <td><?php echo $sale['items_count']; ?></td>
                                                <td>Rs. <?php echo number_format($sale['net_amount'], 2); ?></td>
                                                <td>
                                                    <?php 
                                                        $badge_class = 'badge-secondary';
                                                        if ($sale['payment_method'] === 'cash') $badge_class = 'badge-success';
                                                        else if ($sale['payment_method'] === 'credit') $badge_class = 'badge-danger';
                                                        else if ($sale['payment_method'] === 'card' || $sale['payment_method'] === 'credit_card') $badge_class = 'badge-info';
                                                        else if ($sale['payment_method'] === 'advance') $badge_class = 'badge-primary';
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $sale['payment_method'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($sale['sale_date'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary view-sale-btn" data-invoice="<?php echo htmlspecialchars($sale['invoice_number']); ?>" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info print-sale-btn" data-invoice="<?php echo htmlspecialchars($sale['invoice_number']); ?>" title="Print Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <a href="#" class="btn btn-sm btn-primary" id="view-all-sales-btn">
                            <i class="fas fa-list mr-1"></i> View All Sales
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-secondary export-sales-btn">
                            <i class="fas fa-file-export mr-1"></i> Export
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Stock Movements Tab -->
            <div class="tab-pane fade <?php echo ($active_tab == 'stock') ? 'show active' : ''; ?>" id="stock" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Stock Movements</h5>
                        <div class="form-inline">
                            <input type="text" class="form-control form-control-sm mr-2" id="stock-movement-search" placeholder="Search movements...">
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-movements">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="movements-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Type</th>
                                        <th>Qty</th>
                                        <th>Reason</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($transactions)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No stock movement records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($transactions as $transaction): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($transaction['product_name'] ?? 'Unknown Product'); ?></td>
                                                <td>
                                                    <?php 
                                                        $type_badge = 'badge-secondary';
                                                        $type_text = ucfirst($transaction['transaction_type']);
                                                        
                                                        if ($transaction['transaction_type'] === 'add') {
                                                            $type_badge = 'badge-success';
                                                            $type_text = 'Added';
                                                        } else if ($transaction['transaction_type'] === 'retrieve') {
                                                            $type_badge = 'badge-warning';
                                                            $type_text = 'Retrieved';
                                                        } else if ($transaction['transaction_type'] === 'return') {
                                                            $type_badge = 'badge-info';
                                                        } else if ($transaction['transaction_type'] === 'transfer') {
                                                            $type_badge = 'badge-primary';
                                                        }
                                                    ?>
                                                    <span class="badge <?php echo $type_badge; ?>"><?php echo $type_text; ?></span>
                                                </td>
                                                <td><?php echo $transaction['quantity']; ?></td>
                                                <td>
                                                    <?php echo ucfirst($transaction['reason'] ?: 'N/A'); ?>
                                                    <?php if ($transaction['customer_name']): ?>
                                                        <small class="text-muted d-block">
                                                            Customer: <?php echo htmlspecialchars($transaction['customer_name']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($transaction['total_amount']): ?>
                                                        Rs. <?php echo number_format($transaction['total_amount'], 2); ?>
                                                    <?php else: ?>
                                                        N/A
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($transaction['transaction_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <a href="#" class="btn btn-sm btn-primary" id="view-all-movements-btn">
                            <i class="fas fa-list mr-1"></i> View All Movements
                        </a>
                        <div class="btn-group ml-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-toggle="dropdown">
                                <i class="fas fa-filter mr-1"></i> Filter
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="#" data-filter="all">All Movements</a>
                                <a class="dropdown-item" href="#" data-filter="add">Added Stock</a>
                                <a class="dropdown-item" href="#" data-filter="retrieve">Retrieved Stock</a>
                                <a class="dropdown-item" href="#" data-filter="return">Returns</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Payments Tab -->
            <div class="tab-pane fade <?php echo ($active_tab == 'payments') ? 'show active' : ''; ?>" id="payments" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payment History</h5>
                        <div class="form-inline">
                            <input type="text" class="form-control form-control-sm mr-2" id="payments-search" placeholder="Search payments...">
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-payments">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="payments-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($payments)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No payment records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['invoice_number']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['customer_name'] ?: 'N/A'); ?></td>
                                                <td>Rs. <?php echo number_format($payment['amount'], 2); ?></td>
                                                <td>
                                                    <?php 
                                                        $badge_class = 'badge-secondary';
                                                        if ($payment['payment_method'] === 'cash') $badge_class = 'badge-success';
                                                        else if ($payment['payment_method'] === 'credit') $badge_class = 'badge-danger';
                                                        else if ($payment['payment_method'] === 'card' || $payment['payment_method'] === 'credit_card') $badge_class = 'badge-info';
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($payment['payment_date'])); ?></td>
                                                <td><?php echo htmlspecialchars($payment['notes'] ?: 'N/A'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Advance Payments Tab -->
            <div class="tab-pane fade <?php echo ($active_tab == 'advance') ? 'show active' : ''; ?>" id="advance" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Advance Payment History</h5>
                        <div class="form-inline">
                            <input type="text" class="form-control form-control-sm mr-2" id="advance-search" placeholder="Search advance payments...">
                            <button class="btn btn-sm btn-outline-secondary" id="refresh-advance">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="advance-table">
                                <thead>
                                    <tr>
                                        <th>Bill #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Current Balance</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($advance_payments)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">No advance payment records found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($advance_payments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['advance_bill_number']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                                <td>Rs. <?php echo number_format($payment['net_amount'], 2); ?></td>
                                                <td>Rs. <?php echo number_format($payment['current_balance'] ?? 0, 2); ?></td>
                                                <td>
                                                    <?php 
                                                        $badge_class = 'badge-secondary';
                                                        if ($payment['payment_type'] === 'cash') $badge_class = 'badge-success';
                                                        else if ($payment['payment_type'] === 'card') $badge_class = 'badge-info';
                                                        else if ($payment['payment_type'] === 'bank') $badge_class = 'badge-primary';
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo ucfirst($payment['payment_type']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y h:i A', strtotime($payment['created_at'])); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-info print-advance-btn" data-bill="<?php echo htmlspecialchars($payment['advance_bill_number']); ?>" title="Print Receipt">
                                                        <i class="fas fa-print"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Tab switching with URL parameter
    $('.nav-tabs a').click(function() {
        const tab = $(this).attr('href').substr(1);
        history.replaceState(null, null, '?section=history&tab=' + tab);
    });
    
    // Filter tables on search
    $('#sales-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('#sales-table tbody tr').each(function() {
            const rowContent = $(this).text().toLowerCase();
            if (rowContent.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#stock-movement-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('#movements-table tbody tr').each(function() {
            const rowContent = $(this).text().toLowerCase();
            if (rowContent.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#payments-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('#payments-table tbody tr').each(function() {
            const rowContent = $(this).text().toLowerCase();
            if (rowContent.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    $('#advance-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        $('#advance-table tbody tr').each(function() {
            const rowContent = $(this).text().toLowerCase();
            if (rowContent.includes(searchTerm)) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });
    
    // Refresh tables
    $('#refresh-sales, #refresh-movements, #refresh-payments, #refresh-advance').click(function() {
        // Show loading spinner
        $(this).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        
        // Reload the page or fetch updated data
        location.reload();
    });
    
    // Filter stock movements by type
    $('.dropdown-item[data-filter]').click(function(e) {
        e.preventDefault();
        const filter = $(this).data('filter');
        
        if (filter === 'all') {
            $('#movements-table tbody tr').show();
        } else {
            $('#movements-table tbody tr').hide();
            $('#movements-table tbody tr').each(function() {
                const type = $(this).find('td:nth-child(2)').text().toLowerCase();
                if (type.includes(filter.toLowerCase())) {
                    $(this).show();
                }
            });
        }
    });
    
    // View sale details
    $('.view-sale-btn').click(function() {
        const invoiceNumber = $(this).data('invoice');
        
        // AJAX call to get sale details
        $.ajax({
            url: 'process/get_sale_details.php',
            type: 'GET',
            data: { invoice: invoiceNumber },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Display sale details in modal or alert
                    let itemsList = '';
                    response.items.forEach(function(item) {
                        itemsList += `
                            <tr>
                                <td>${item.product_name}</td>
                                <td>${item.quantity} ${item.free_quantity > 0 ? '(+' + item.free_quantity + ' free)' : ''}</td>
                                <td class="text-right">Rs. ${parseFloat(item.unit_price).toFixed(2)}</td>
                                <td class="text-right">Rs. ${parseFloat(item.subtotal).toFixed(2)}</td>
                            </tr>
                        `;
                    });
                    
                    Swal.fire({
                        title: 'Sale Details: ' + invoiceNumber,
                        html: `
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-right">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${itemsList}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="3" class="text-right">Subtotal:</th>
                                            <td class="text-right">Rs. ${parseFloat(response.sale.total_amount).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-right">Discount:</th>
                                            <td class="text-right">Rs. ${parseFloat(response.sale.discount_amount).toFixed(2)}</td>
                                        </tr>
                                        <tr>
                                            <th colspan="3" class="text-right">Total:</th>
                                            <td class="text-right">Rs. ${parseFloat(response.sale.net_amount).toFixed(2)}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-3">
                                <p><strong>Customer:</strong> ${response.sale.customer_name || 'Walk-in Customer'}</p>
                                <p><strong>Payment Method:</strong> ${response.sale.payment_method}</p>
                                <p><strong>Date:</strong> ${new Date(response.sale.sale_date).toLocaleString()}</p>
                            </div>
                        `,
                        width: '600px',
                        showCloseButton: true,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to load sale details'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load sale details'
                });
            }
        });
    });
    
    // Print sale receipt
    $('.print-sale-btn').click(function() {
        const invoiceNumber = $(this).data('invoice');
        
        // Open receipt in new window
        window.open(`../invoice/pos_rep_invoice.php?invoice=${invoiceNumber}`, '_blank', 'width=350,height=600');
    });
    
    // Print advance payment receipt
    $('.print-advance-btn').click(function() {
        const billNumber = $(this).data('bill');
        
        // Open receipt in new window
        window.open(`../invoice/advance_bill_pos.php?advance_bill_number=${billNumber}`, '_blank', 'width=350,height=600');
    });
    
    // View all sales
    $('#view-all-sales-btn').click(function(e) {
        e.preventDefault();
        
        // Load full sales history page with more filters and pagination
        // This would typically be a separate page with more detailed listings
        Swal.fire({
            icon: 'info',
            title: 'Coming Soon',
            text: 'Complete sales history with advanced filters will be available soon.'
        });
    });
    
    // Export functionality (placeholder for now)
    $('.export-sales-btn').click(function(e) {
        e.preventDefault();
        
        Swal.fire({
            icon: 'info',
            title: 'Export Options',
            html: `
                <div class="text-left">
                    <p>Select export format:</p>
                    <div class="mb-3">
                        <a href="#" class="btn btn-outline-primary btn-block export-csv">
                            <i class="fas fa-file-csv mr-2"></i> Export as CSV
                        </a>
                    </div>
                    <div>
                        <a href="#" class="btn btn-outline-danger btn-block export-pdf">
                            <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                        </a>
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true
        });
    });
});
</script>

<!-- Add necessary supporting script for SweetAlert2 if not already included -->
<script>
// Check if SweetAlert2 is already loaded, if not load it
if (typeof Swal === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"><\/script>');
}
</script>
