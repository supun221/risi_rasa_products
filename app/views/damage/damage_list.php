<!DOCTYPE html>
<html>

<head>
    <?php
    require_once '../header1.php';
    ?>
    <title>Damage List</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Professional styling for damage list page - Red Rose Theme */
        body {
            background-color: #faf7f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-fluid {
            padding: 20px;
        }

        .page-header {
    
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(229, 62, 62, 0.2);
        }

        .page-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }

        .page-header h1 i {
            margin-right: 15px;
            font-size: 2.2rem;
        }

        .controls-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.1);
            border: 1px solid #fed7d7;
        }

        .search-filters {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: end;
            margin-bottom: 20px;
        }

        .search-group {
            flex: 1;
            min-width: 200px;
        }

        .search-group label {
            font-weight: 600;
            color: #742a2a;
            margin-bottom: 5px;
            display: block;
        }

        .search-input {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #fed7d7;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            outline: none;
            border-color: #e53e3e;
            box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(229, 62, 62, 0.3);
        }

        .btn-secondary {
            background: #9f7aea;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #805ad5;
            transform: translateY(-2px);
        }

        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.1);
            border: 1px solid #fed7d7;
        }

        .table {
            margin: 0;
            border-radius: 15px;
            overflow: hidden;
        }

        .table thead th {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 18px 15px;
            font-size: 13px;
        }

        .table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #fed7d7;
        }

        .table tbody tr:hover {
            background-color: #fef5e7;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.05);
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border: none;
        }

        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-btn {
            padding: 8px 10px;
            border-radius: 50%;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .view-btn {
            background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);
            color: white;
        }

        .view-btn:hover {
            color: white;
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 4px 15px rgba(56, 178, 172, 0.3);
        }

        .edit-btn {
            background: linear-gradient(135deg, #d69e2e 0%, #b7791f 100%);
            color: white;
        }

        .edit-btn:hover {
            color: white;
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 4px 15px rgba(214, 158, 46, 0.3);
        }

        .delete-btn {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
        }

        .delete-btn:hover {
            color: white;
            transform: translateY(-2px) scale(1.1);
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.3);
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-quantity {
            background: linear-gradient(135deg, #38b2ac 0%, #319795 100%);
            color: white;
        }

        .badge-price {
            background: linear-gradient(135deg, #d69e2e 0%, #b7791f 100%);
            color: white;
        }

        .no-data {
            text-align: center;
            padding: 50px;
            color: #742a2a;
            font-style: italic;
        }

        .no-data i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
            color: #e53e3e;
        }

        .stats-cards {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .stat-card {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(229, 62, 62, 0.1);
            border: 1px solid #fed7d7;
            text-align: center;
        }

        .stat-card .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #e53e3e;
            margin-bottom: 5px;
        }

        .stat-card .stat-label {
            color: #742a2a;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 12px;
        }

        .search-results-info {
            background: #fef5e7;
            color: #975a16;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            font-weight: 500;
            display: none;
            border: 1px solid #fbd38d;
        }

        .search-results-info i {
            margin-right: 8px;
        }

        /* View More Modal Styles */
        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #fed7d7;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #742a2a;
        }

        .detail-value {
            color: #2d3748;
            background: #fef5e7;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .modal-header {
            background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }

        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 8px 25px rgba(229, 62, 62, 0.2);
        }

        @media (max-width: 768px) {
            .search-filters {
                flex-direction: column;
            }
            
            .search-group {
                min-width: 100%;
            }
            
            .action-buttons {
                flex-direction: row;
                gap: 5px;
            }
            
            .stats-cards {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-heartbeat"></i> Damage Management</h1>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number" id="total-damages">0</div>
                <div class="stat-label">Total Damages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-quantity">0</div>
                <div class="stat-label">Total Quantity</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="total-value">$0.00</div>
                <div class="stat-label">Total Value</div>
            </div>
        </div>

        <!-- Controls Section -->
        <div class="controls-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-search"></i> Search & Filters</h5>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDamageModal">
                    <i class="fas fa-plus-circle"></i> Add Damage
                </button>
            </div>
            
            <div class="search-filters">
                <div class="search-group">
                    <label for="search-product">Product Name</label>
                    <input type="text" class="search-input" id="search-product" placeholder="Search by product name...">
                </div>
                <div class="search-group">
                    <label for="search-description">Description</label>
                    <input type="text" class="search-input" id="search-description" placeholder="Search by description...">
                </div>
                <div class="search-group">
                    <label for="search-barcode">Barcode</label>
                    <input type="text" class="search-input" id="search-barcode" placeholder="Search by barcode...">
                </div>
                <div class="search-group">
                    <label for="search-quantity">Min Quantity</label>
                    <input type="number" class="search-input" id="search-quantity" placeholder="Min quantity...">
                </div>
                <div class="search-group" style="flex: 0.5;">
                    <label>&nbsp;</label>
                    <button type="button" class="btn btn-secondary w-100" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
            </div>
            
            <div class="search-results-info" id="search-results-info">
                <i class="fas fa-info-circle"></i>
                <span id="search-results-text">Showing all damages</span>
            </div>
        </div>
    <?php
    require_once 'add_damage.php'; // Include add damage modal

    // Database connection
    require_once '../../../config/databade.php';

    // Fetch damages from the database
    $damages = [];
    $query = "SELECT id, product_name, damage_description, damage_quantity, price, barcode, branch, date FROM damages ORDER BY date DESC";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $damages[] = $row;
        }
    } else {
        echo "<p>Error fetching damages: " . mysqli_error($conn) . "</p>";
    }

    include 'update_damage.php'; // Include update damage modal
    ?>

    <!-- View Details Modal -->
    <div class="modal fade" id="viewDamageModal" tabindex="-1" role="dialog" aria-labelledby="viewDamageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewDamageModalLabel">
                        <i class="fas fa-eye"></i> Damage Details
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="damage-details-content">
                    <!-- Content will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="table" id="damages-table">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> No</th>
                    <th><i class="fas fa-box"></i> Product Name</th>
                    <th><i class="fas fa-barcode"></i> Barcode</th>
                    <th><i class="fas fa-file-alt"></i> Description</th>
                    <th><i class="fas fa-cubes"></i> Quantity</th>
                    <th><i class="fas fa-dollar-sign"></i> Price</th>
                    <th><i class="fas fa-cogs"></i> Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($damages)) {
                    foreach ($damages as $index => $damage) {
                        echo '<tr id="damage-row-' . htmlspecialchars($damage['id']) . '" class="damage-row">';
                        echo '<td>' . ($index + 1) . '</td>';
                        echo '<td class="product-name">' . htmlspecialchars($damage['product_name']) . '</td>';
                        echo '<td class="barcode">' . htmlspecialchars($damage['barcode']) . '</td>';
                        echo '<td class="description">' . htmlspecialchars($damage['damage_description']) . '</td>';
                        echo '<td><span class="badge badge-quantity">' . htmlspecialchars($damage['damage_quantity']) . '</span></td>';
                        echo '<td><span class="badge badge-price">$' . number_format($damage['price'], 2) . '</span></td>';
                        echo '<td class="action-buttons">
                            <button onclick="viewDamageDetails(' . htmlspecialchars($damage['id']) . ')" class="action-btn view-btn" title="View Details">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button onclick="openUpdateDamageModal(' . htmlspecialchars($damage['id']) . ')" class="action-btn edit-btn" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button onclick="confirmDelete(' . htmlspecialchars($damage['id']) . ')" class="action-btn delete-btn" title="Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="7" class="no-data">
                        <i class="fas fa-heart-broken"></i><br>
                        No damage records found
                    </td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    </div> <!-- End container-fluid -->

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Calculate and display statistics
        function updateStatistics() {
            const rows = document.querySelectorAll('#damages-table tbody tr.damage-row');
            let totalDamages = rows.length;
            let totalQuantity = 0;
            let totalValue = 0;

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const quantityText = row.querySelector('.badge-quantity').textContent;
                    const priceText = row.querySelector('.badge-price').textContent.replace('$', '').replace(',', '');
                    
                    const quantity = parseInt(quantityText) || 0;
                    const price = parseFloat(priceText) || 0;
                    
                    totalQuantity += quantity;
                    totalValue += (quantity * price);
                }
            });

            document.getElementById('total-damages').textContent = totalDamages;
            document.getElementById('total-quantity').textContent = totalQuantity;
            document.getElementById('total-value').textContent = '$' + totalValue.toFixed(2);
        }

        // Search and filter functionality
        function performSearch() {
            const productFilter = document.getElementById('search-product').value.toLowerCase();
            const descriptionFilter = document.getElementById('search-description').value.toLowerCase();
            const barcodeFilter = document.getElementById('search-barcode').value.toLowerCase();
            const quantityFilter = document.getElementById('search-quantity').value;
            
            const rows = document.querySelectorAll('#damages-table tbody tr.damage-row');
            let visibleCount = 0;

            rows.forEach(row => {
                const productName = row.querySelector('.product-name').textContent.toLowerCase();
                const description = row.querySelector('.description').textContent.toLowerCase();
                const barcode = row.querySelector('.barcode').textContent.toLowerCase();
                const quantity = parseInt(row.querySelector('.badge-quantity').textContent) || 0;

                const productMatch = productName.includes(productFilter);
                const descriptionMatch = description.includes(descriptionFilter);
                const barcodeMatch = barcode.includes(barcodeFilter);
                const quantityMatch = !quantityFilter || quantity >= parseInt(quantityFilter);

                if (productMatch && descriptionMatch && barcodeMatch && quantityMatch) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update search results info
            const searchInfo = document.getElementById('search-results-info');
            const searchText = document.getElementById('search-results-text');
            
            if (productFilter || descriptionFilter || barcodeFilter || quantityFilter) {
                searchInfo.style.display = 'block';
                searchText.textContent = `Showing ${visibleCount} of ${rows.length} damage records`;
            } else {
                searchInfo.style.display = 'none';
            }

            updateStatistics();
        }

        // Clear all filters
        function clearFilters() {
            document.getElementById('search-product').value = '';
            document.getElementById('search-description').value = '';
            document.getElementById('search-barcode').value = '';
            document.getElementById('search-quantity').value = '';
            
            document.querySelectorAll('#damages-table tbody tr.damage-row').forEach(row => {
                row.style.display = '';
            });
            
            document.getElementById('search-results-info').style.display = 'none';
            updateStatistics();
        }

        // Add event listeners for real-time search
        document.addEventListener('DOMContentLoaded', function() {
            updateStatistics();
            
            document.getElementById('search-product').addEventListener('input', performSearch);
            document.getElementById('search-description').addEventListener('input', performSearch);
            document.getElementById('search-barcode').addEventListener('input', performSearch);
            document.getElementById('search-quantity').addEventListener('input', performSearch);
        });

        // View damage details function
        function viewDamageDetails(damageId) {
            // Show loading
            Swal.fire({
                title: 'Loading Details...',
                text: 'Please wait while we fetch the damage details.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '../../controllers/damage_controller.php',
                type: 'post',
                contentType: 'application/json',
                data: JSON.stringify({
                    action: 'get_by_id',
                    id: damageId
                }),
                success: function (response) {
                    Swal.close();
                    
                    if (response.status === 'success') {
                        const damage = response.data;
                        
                        // Format the details content
                        const detailsHTML = `
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-hashtag"></i> Damage ID:</span>
                                <span class="detail-value">#${damage.id}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-box"></i> Product Name:</span>
                                <span class="detail-value">${damage.product_name}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-barcode"></i> Barcode:</span>
                                <span class="detail-value">${damage.barcode}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-file-alt"></i> Description:</span>
                                <span class="detail-value">${damage.damage_description}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-cubes"></i> Quantity:</span>
                                <span class="detail-value badge badge-quantity">${damage.damage_quantity}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-dollar-sign"></i> Unit Price:</span>
                                <span class="detail-value badge badge-price">$${parseFloat(damage.price).toFixed(2)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-calculator"></i> Total Value:</span>
                                <span class="detail-value badge" style="background: #e53e3e; color: white;">$${(damage.damage_quantity * damage.price).toFixed(2)}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-store"></i> Branch:</span>
                                <span class="detail-value">${damage.branch || 'N/A'}</span>
                            </div>
                            <div class="detail-item">
                                <span class="detail-label"><i class="fas fa-calendar"></i> Date Created:</span>
                                <span class="detail-value">${new Date(damage.date).toLocaleString()}</span>
                            </div>
                        `;
                        
                        document.getElementById('damage-details-content').innerHTML = detailsHTML;
                        $('#viewDamageModal').modal('show');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: response.message
                        });
                    }
                },
                error: function () {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'There was a problem fetching the damage details.'
                    });
                }
            });
        }

        function confirmDelete(damageId) {
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Deleting...',
                        text: 'Please wait while we delete the damage record.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    fetch('../../controllers/damage_controller.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                action: 'delete',
                                id: damageId,
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                const row = document.getElementById('damage-row-' + damageId);
                                if (row) {
                                    row.style.animation = 'fadeOut 0.5s ease-out';
                                    setTimeout(() => {
                                        row.remove();
                                        updateStatistics();
                                        performSearch(); // Reapply filters
                                    }, 500);
                                }
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: data.message
                                });
                            }
                        })
                        .catch((error) => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong. Please try again.'
                            });
                        });
                }
            });
        }

        // Function to open the update modal and populate the form
function openUpdateDamageModal(damageId) {
    // Show loading
    Swal.fire({
        title: 'Loading...',
        text: 'Please wait while we fetch the damage details.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '../../controllers/damage_controller.php',
        type: 'post',
        contentType: 'application/json',
        data: JSON.stringify({
            action: 'get_by_id',
            id: damageId
        }),
        success: function (response) {
            Swal.close();
            
            if (response.status === 'success') {
                const damage = response.data;

                // Populate form fields with fetched data
                $('#update-damage-id').val(damage.id);
                $('#update-product-name').val(damage.product_name);
                $('#update-damage-description').val(damage.damage_description);
                $('#update-damage-quantity').val(damage.damage_quantity);
                $('#update-damage-price').val(damage.price);
                $('#update-barcode').val(damage.barcode);

                // Show the modal
                $('#updateDamageModal').modal('show');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: response.message
                });
            }
        },
        error: function () {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'There was a problem fetching the damage details.'
            });
        }
    });
}

document.addEventListener("keydown", function (event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });

    </script>

    <style>
        @keyframes fadeOut {
            from { opacity: 1; transform: scale(1); }
            to { opacity: 0; transform: scale(0.95); }
        }
    </style>
</body>

</html>