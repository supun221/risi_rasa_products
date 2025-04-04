<?php
// Get stock overview data
require_once '../../../../config/databade.php';
session_start();

// Get rep_id from session
$rep_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 1;

// Get summary of lorry stock
$stmt = $conn->prepare("
    SELECT COUNT(*) as total_products,
           SUM(quantity) as total_quantity,
           SUM(quantity * unit_price) as total_value
    FROM lorry_stock
    WHERE rep_id = ? AND status = 'active'
");
$stmt->bind_param("i", $rep_id);
$stmt->execute();
$result = $stmt->get_result();
$stock_summary = $result->fetch_assoc();

// Get recent additions
$stmt = $conn->prepare("
    SELECT product_name, quantity, date_added
    FROM lorry_stock
    WHERE rep_id = ? AND status = 'active'
    ORDER BY date_added DESC
    LIMIT 5
");
$stmt->bind_param("i", $rep_id);
$stmt->execute();
$recent_result = $stmt->get_result();
$recent_additions = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_additions[] = $row;
}
?>

<style>
/* Custom gradient styles for cards */
.gradient-card {
    border: none;
    border-radius: 10px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}
.gradient-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 20px rgba(0,0,0,0.12);
}
.gradient-primary {
    background: linear-gradient(135deg, #3a7bd5, #6c5ce7);
}
.gradient-info {
    background: linear-gradient(135deg, #36d1dc, #5b86e5);
}
.gradient-success {
    background: linear-gradient(135deg, #11998e, #38ef7d);
}
.card-icon {
    opacity: 0.8;
    transition: opacity 0.3s ease, transform 0.3s ease;
}
.gradient-card:hover .card-icon {
    opacity: 1;
    transform: scale(1.1);
}
.card-value {
    font-size: 2rem;
    font-weight: 700;
    letter-spacing: 0.5px;
}
.card-label {
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    opacity: 0.8;
}
/* Custom responsive design for cards row */
.overview-cards {
    display: flex;
    flex-wrap: nowrap;
    margin: 0 -10px;
    overflow-x: auto;
    padding-bottom: 10px;
}
.overview-card-container {
    flex: 0 0 33.333%;
    padding: 0 10px;
    min-width: 200px;
}
@media (max-width: 767px) {
    .overview-cards {
        margin: 0 -5px;
    }
    .overview-card-container {
        padding: 0 5px;
        min-width: 160px;
    }
    .card-value {
        font-size: 1.6rem;
    }
    .card-icon {
        font-size: 1.8rem !important;
    }
}
</style>

<div class="row">
    <!-- Stock Summary Cards -->
    <div class="col-12 mb-4">
        <h5 class="text-muted mb-3">Stock Summary</h5>
        
        <div class="overview-cards">
            <div class="overview-card-container">
                <div class="card text-white gradient-card gradient-primary h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-boxes fa-2x card-icon"></i>
                            </div>
                            <div>
                                <div class="card-label">Total Products</div>
                                <div class="card-value"><?php echo number_format($stock_summary['total_products'] ?? 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overview-card-container">
                <div class="card text-white gradient-card gradient-info h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-cubes fa-2x card-icon"></i>
                            </div>
                            <div>
                                <div class="card-label">Items in Stock</div>
                                <div class="card-value"><?php echo number_format($stock_summary['total_quantity'] ?? 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overview-card-container">
                <div class="card text-white gradient-card gradient-success h-100">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-money-bill-wave fa-2x card-icon"></i>
                            </div>
                            <div>
                                <div class="card-label">Stock Value</div>
                                <div class="card-value">Rs. <?php echo number_format(($stock_summary['total_value'] ?? 0), 0); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Stock Additions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Recent Stock Additions</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Date Added</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_additions)): ?>
                                <tr>
                                    <td colspan="3" class="text-center">No recent additions found</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($recent_additions as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo date('M d, Y h:i A', strtotime($item['date_added'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-light">
                <a href="#" class="btn btn-sm btn-primary" onclick="$('#stock-nav').click(); return false;">
                    <i class="fas fa-boxes mr-1"></i> View All Stock
                </a>
            </div>
        </div>
    </div>
</div>
