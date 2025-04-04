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

<!-- Stock Movements Modal -->
<div class="modal fade" id="movementsModal" tabindex="-1" role="dialog" aria-labelledby="movementsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="movementsModalLabel">All Stock Movements</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Filters -->
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="movement-date-start">From Date</label>
                            <input type="date" id="movement-date-start" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="movement-date-end">To Date</label>
                            <input type="date" id="movement-date-end" class="form-control form-control-sm">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="movement-type-filter">Transaction Type</label>
                            <select id="movement-type-filter" class="form-control form-control-sm">
                                <option value="">All Types</option>
                                <option value="add">Added Stock</option>
                                <option value="retrieve">Retrieved Stock</option>
                                <option value="return">Returns</option>
                                <option value="transfer">Transfers</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="movement-product-filter">Product Name</label>
                            <input type="text" id="movement-product-filter" class="form-control form-control-sm" placeholder="Search products...">
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-12 text-right">
                        <button id="apply-movement-filters" class="btn btn-sm btn-primary">
                            <i class="fas fa-filter mr-1"></i> Apply Filters
                        </button>
                        <button id="reset-movement-filters" class="btn btn-sm btn-secondary">
                            <i class="fas fa-redo mr-1"></i> Reset
                        </button>
                    </div>
                </div>
                
                <!-- Movements Table -->
                <div class="table-responsive">
                    <table class="table table-sm table-striped table-hover" id="all-movements-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Item Code</th>
                                <th>Type</th>
                                <th>Qty</th>
                                <th>Reason</th>
                                <th>Customer/Note</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody id="all-movements-body">
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        <span id="movement-showing-info">Showing 0 to 0 of 0 entries</span>
                    </div>
                    <div>
                        <nav>
                            <ul class="pagination pagination-sm" id="movement-pagination">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#" tabindex="-1">Previous</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-success" id="export-movements-csv">
                    <i class="fas fa-file-csv mr-1"></i> Export as CSV
                </button>
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
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
    
    // View all movements - new implementation
    $('#view-all-movements-btn').click(function(e) {
        e.preventDefault();
        
        // Set default date range (last 30 days)
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        
        $('#movement-date-start').val(formatDate(thirtyDaysAgo));
        $('#movement-date-end').val(formatDate(today));
        
        // Load initial data and show modal
        loadMovementsData();
        $('#movementsModal').modal('show');
    });
    
    // Helper function to format date as YYYY-MM-DD
    function formatDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // Apply movement filters
    $('#apply-movement-filters').click(function() {
        loadMovementsData(1); // Reset to first page when applying filters
    });
    
    // Reset movement filters
    $('#reset-movement-filters').click(function() {
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
        
        $('#movement-date-start').val(formatDate(thirtyDaysAgo));
        $('#movement-date-end').val(formatDate(today));
        $('#movement-type-filter').val('');
        $('#movement-product-filter').val('');
        
        loadMovementsData(1);
    });
    
    // Handle pagination clicks
    $(document).on('click', '#movement-pagination .page-link', function(e) {
        e.preventDefault();
        
        const page = $(this).data('page');
        if (page) {
            loadMovementsData(page);
        }
    });
    
    // Export movements as CSV
    $('#export-movements-csv').click(function() {
        const startDate = $('#movement-date-start').val();
        const endDate = $('#movement-date-end').val();
        const type = $('#movement-type-filter').val();
        const product = $('#movement-product-filter').val();
        
        const url = `process/export_movements_csv.php?start_date=${startDate}&end_date=${endDate}&type=${type}&product=${encodeURIComponent(product)}`;
        window.location = url;
    });
    
    // Function to load movements data with AJAX
    function loadMovementsData(page = 1) {
        const startDate = $('#movement-date-start').val();
        const endDate = $('#movement-date-end').val();
        const type = $('#movement-type-filter').val();
        const product = $('#movement-product-filter').val();
        
        // Show loading spinner
        $('#all-movements-body').html(`
            <tr>
                <td colspan="8" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </td>
            </tr>
        `);
        
        // Fetch data with AJAX
        $.ajax({
            url: 'process/get_all_movements.php',
            type: 'GET',
            data: {
                page: page,
                start_date: startDate,
                end_date: endDate,
                type: type,
                product: product
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    displayMovementsData(response);
                } else {
                    $('#all-movements-body').html(`
                        <tr>
                            <td colspan="8" class="text-center text-danger">
                                ${response.message || 'Failed to load movements data'}
                            </td>
                        </tr>
                    `);
                }
            },
            error: function() {
                $('#all-movements-body').html(`
                    <tr>
                        <td colspan="8" class="text-center text-danger">
                            Failed to connect to server. Please try again.
                        </td>
                    </tr>
                `);
            }
        });
    }
    
    // Function to display movements data in the table
    function displayMovementsData(response) {
        const movements = response.movements;
        const pagination = response.pagination;
        
        // Clear table body
        $('#all-movements-body').empty();
        
        if (movements.length === 0) {
            $('#all-movements-body').html(`
                <tr>
                    <td colspan="8" class="text-center">
                        No stock movement records found matching the selected criteria
                    </td>
                </tr>
            `);
        } else {
            // Add rows to table
            movements.forEach(function(movement) {
                let typeBadge = 'badge-secondary';
                let typeText = ucfirst(movement.transaction_type);
                
                if (movement.transaction_type === 'add') {
                    typeBadge = 'badge-success';
                    typeText = 'Added';
                } else if (movement.transaction_type === 'retrieve') {
                    typeBadge = 'badge-warning';
                    typeText = 'Retrieved';
                } else if (movement.transaction_type === 'return') {
                    typeBadge = 'badge-info';
                    typeText = 'Return';
                } else if (movement.transaction_type === 'transfer') {
                    typeBadge = 'badge-primary';
                    typeText = 'Transfer';
                }
                
                // Modified this line to use only customer_name since notes doesn't exist
                const customerInfo = escapeHtml(movement.customer_name || 'N/A');
                
                $('#all-movements-body').append(`
                    <tr>
                        <td>${escapeHtml(movement.product_name || 'Unknown Product')}</td>
                        <td>${escapeHtml(movement.itemcode || 'N/A')}</td>
                        <td>
                            <span class="badge ${typeBadge}">${typeText}</span>
                        </td>
                        <td>${movement.quantity}</td>
                        <td>${ucfirst(movement.reason || 'N/A')}</td>
                        <td>${customerInfo}</td>
                        <td>
                            ${movement.total_amount ? 'Rs. ' + parseFloat(movement.total_amount).toFixed(2) : 'N/A'}
                        </td>
                        <td>${formatDateTime(movement.transaction_date)}</td>
                    </tr>
                `);
            });
            
            // Update pagination info
            $('#movement-showing-info').text(
                `Showing ${pagination.from} to ${pagination.to} of ${pagination.total} entries`
            );
            
            // Build pagination links
            const paginationHtml = buildPaginationLinks(pagination);
            $('#movement-pagination').html(paginationHtml);
        }
    }
    
    // Helper function to build pagination links
    function buildPaginationLinks(pagination) {
        let html = '';
        const currentPage = pagination.current_page;
        const lastPage = pagination.last_page;
        
        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage - 1}" ${currentPage === 1 ? 'tabindex="-1"' : ''}>Previous</a>
            </li>
        `;
        
        // Page numbers
        const startPage = Math.max(1, currentPage - 2);
        const endPage = Math.min(lastPage, currentPage + 2);
        
        for (let i = startPage; i <= endPage; i++) {
            html += `
                <li class="page-item ${i === currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `;
        }
        
        // Next button
        html += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="${currentPage + 1}" ${currentPage === lastPage ? 'tabindex="-1"' : ''}>Next</a>
            </li>
        `;
        
        return html;
    }
    
    // Helper function to capitalize first letter
    function ucfirst(string) {
        if (!string) return '';
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
    
    // Helper function to format date and time
    function formatDateTime(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return date.toLocaleString();
    }
    
    // Helper function to escape HTML entities
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    }
    
    // Export functionality - Updated implementation with better script loading
    $('.export-sales-btn').click(function(e) {
        e.preventDefault();
        
        // Safety check for SweetAlert2 - display native dialog if not available
        if (typeof Swal === 'undefined') {
            const format = confirm('Select export format:\n\nOK - Export as CSV\nCancel - Export as PDF');
            
            if (format) {
                promptExportCSV();
            } else {
                promptExportPDF();
            }
            return;
        }
        
        // If SweetAlert is available, use the nice dialog
        Swal.fire({
            icon: 'info',
            title: 'Export Options',
            html: `
                <div class="text-left">
                    <p>Select export format:</p>
                    <form id="export-form">
                        <div class="form-group">
                            <label for="export-start-date">Start Date:</label>
                            <input type="date" id="export-start-date" class="form-control" value="${getDefaultStartDate()}">
                        </div>
                        <div class="form-group">
                            <label for="export-end-date">End Date:</label>
                            <input type="date" id="export-end-date" class="form-control" value="${getDefaultEndDate()}">
                        </div>
                        <div class="form-group">
                            <label for="export-payment-method">Payment Method (Optional):</label>
                            <select id="export-payment-method" class="form-control">
                                <option value="">All Methods</option>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="credit">Credit</option>
                                <option value="advance">Advance</option>
                            </select>
                        </div>
                        
                        <div class="mb-3 mt-4">
                            <button type="button" class="btn btn-outline-primary btn-block export-csv-btn">
                                <i class="fas fa-file-csv mr-2"></i> Export as CSV
                            </button>
                        </div>
                        <div>
                            <button type="button" class="btn btn-outline-danger btn-block export-pdf-btn">
                                <i class="fas fa-file-pdf mr-2"></i> Export as PDF
                            </button>
                        </div>
                    </form>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: '500px'
        });
    });
    
    // Helper functions for export dates
    function getDefaultStartDate() {
        const date = new Date();
        date.setDate(date.getDate() - 30);
        return date.toISOString().split('T')[0];
    }
    
    function getDefaultEndDate() {
        const date = new Date();
        return date.toISOString().split('T')[0];
    }
    
    // Handle CSV export button click - using direct event handler
    $(document).on('click', '.export-csv-btn', function() {
        promptExportCSV();
    });
    
    // Handle PDF export button click - using direct event handler  
    $(document).on('click', '.export-pdf-btn', function() {
        promptExportPDF();
    });
    
    // Function to handle CSV export process
    function promptExportCSV() {
        const startDate = $('#export-start-date').val() || getDefaultStartDate();
        const endDate = $('#export-end-date').val() || getDefaultEndDate();
        const paymentMethod = $('#export-payment-method').val() || '';
        
        // Validate dates
        if (!validateDateRange(startDate, endDate)) {
            return;
        }
        
        // Create and submit the form for download
        const url = `process/export_sales_csv.php?start_date=${startDate}&end_date=${endDate}&payment_method=${paymentMethod}`;
        window.location = url;
    }
    
    // Function to handle PDF export process
    function promptExportPDF() {
        const startDate = $('#export-start-date').val() || getDefaultStartDate();
        const endDate = $('#export-end-date').val() || getDefaultEndDate();
        const paymentMethod = $('#export-payment-method').val() || '';
        
        // Validate dates
        if (!validateDateRange(startDate, endDate)) {
            return;
        }
        
        // Create and submit the form for download
        const url = `process/export_sales_pdf.php?start_date=${startDate}&end_date=${endDate}&payment_method=${paymentMethod}`;
        window.location = url;
    }
    
    // Validate date range
    function validateDateRange(startDate, endDate) {
        if (!startDate || !endDate) {
            alert('Please select both start and end dates');
            return false;
        }
        
        if (startDate > endDate) {
            alert('Start date cannot be after end date');
            return false;
        }
        
        return true;
    }
});
</script>

<!-- Properly include SweetAlert2 -->
<script>
(function() {
    // Check if SweetAlert is already loaded
    if (typeof Swal !== 'undefined') {
        // SweetAlert is already loaded, no need to do anything
        return;
    }
    
    // Create script element instead of using document.write
    var sweetAlertScript = document.createElement('script');
    sweetAlertScript.src = 'https://cdn.jsdelivr.net/npm/sweetalert2@11';
    sweetAlertScript.async = true;
    
    // Append to head
    document.head.appendChild(sweetAlertScript);
})();
</script>
