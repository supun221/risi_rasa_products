<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RisiRasa POS - Lorry Stock Management</title>
    <link href="https://fonts.googleapis.com/css?family=Cardo:400i|Rubik:400,500,700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            /* Uncomment if needed later */
            /* display: flex; flex-direction: column; align-items: center; background-color: #f0f0f0; */
            background-color: #f8f9fa;
        }

        .page-content {
            display: grid;
            grid-gap: 1.25rem;
            padding: 1rem;
            max-width: 1300px;
            margin: 15px auto;
            font-family: 'Rubik', sans-serif;
        }

        @media (min-width: 600px) {
            .page-content {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 900px) {
            .page-content {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .page-content {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .card {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow: hidden;
            width: 100%;
            min-width: 260px;
            text-align: center;
            color: #fff;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            min-height: 380px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
        }

        .card:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-size: cover;
            background-position: center;
            transition: transform 0.8s cubic-bezier(0.19, 1, 0.22, 1);
            pointer-events: none;
            background-color: #6a1b1b; /* Darker red, more professional */
        }

        .card:after {
            content: '';
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            background-image: linear-gradient(to bottom,
                    rgba(0, 0, 0, 0.1) 0%,
                    rgba(0, 0, 0, 0.3) 40%,
                    rgba(0, 0, 0, 0.7) 100%);
        }

        .initial-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 100%;
            z-index: 1;
            opacity: 1;
            transition: opacity 0.3s ease;
        }

        /* Fix for total stock being overridden on hover */
        .card:hover .initial-content {
            opacity: 0;
            pointer-events: none; /* Prevent interaction with hidden content */
        }

        /* Enhanced content style to properly show information */
        .content {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            height: 100%;
            padding: 1.25rem 1rem;
            transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            z-index: 1;
            opacity: 1; /* Ensure content is always visible */
        }

        /* Handle information display on non-hover state */
        @media (hover: hover) {
            .content {
                transform: translateY(calc(100% - 4.5rem));
                transition: transform 0.5s cubic-bezier(0.19, 1, 0.22, 1);
            }

            .card:hover .content,
            .card:focus-within .content {
                transform: translateY(0);
            }
        }

        .content>*+* {
            margin-top: 0.75rem;
        }

        .title {
            font-size: 1.4rem;
            font-weight: 600;
            line-height: 1.2;
            margin-bottom: 0.5rem;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        }

        .copy {
            font-family: 'Rubik', sans-serif;
            font-size: 0.95rem;
            font-weight: 400;
            line-height: 1.35;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 0.25rem;
        }

        .sales {
            font-size: 1.5rem;
            font-weight: 600;
            color: #fff;
            margin: 0.5rem 0;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        .btn {
            cursor: pointer;
            margin-top: 1rem;
            padding: 0.65rem 1.25rem;
            font-size: 0.85rem;
            font-weight: 500;
            letter-spacing: 0.025rem;
            text-transform: uppercase;
            color: white;
            background-color: #8b0000;
            border: none;
            border-radius: 6px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .btn:hover {
            background-color: #a30000;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        .btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 0, 0, 0.3);
        }

        /* Enhanced popup styles */
        .popup-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            z-index: 1000;
            justify-content: center;
            align-items: center;
            backdrop-filter: blur(3px);
        }

        .popup-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 12px;
            width: 85%;
            max-width: 950px;
            max-height: 85vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            animation: popupFadeIn 0.3s ease;
        }

        @keyframes popupFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .popup-close {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 1.5rem;
            color: #8b0000;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.3s;
        }

        .popup-close:hover {
            background-color: rgba(139, 0, 0, 0.1);
        }

        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .stock-table th,
        .stock-table td {
            border: 1px solid #e5e5e5;
            padding: 10px;
            text-align: left;
        }

        .stock-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        .stock-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .stock-table tr:hover {
            background-color: #f1f1f1;
        }

        #popup-title {
            color: #333;
            font-size: 1.6rem;
            margin-bottom: 20px;
            border-bottom: 2px solid #8b0000;
            padding-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php
    // Include database configuration
    require_once '../../../config/databade.php'; // Correct to 'database.php' in your setup

    // Check database connection
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Query to fetch rep info and their total stock quantities and amounts
    $sql = "SELECT r.id as rep_id, r.username as rep_name, r.telephone as contact_number, r.Email as email, 
            COUNT(DISTINCT ls.product_name) AS total_products,
            SUM(ls.quantity) AS total_quantity,
            SUM(ls.quantity * ls.unit_price) AS total_amount
            FROM signup r
            LEFT JOIN lorry_stock ls ON r.id = ls.rep_id AND ls.status = 'active' /* Ensure join condition includes active stock */
            WHERE r.job_role = 'rep'
            GROUP BY r.id, r.username, r.telephone, r.Email
            HAVING SUM(ls.quantity) IS NOT NULL"; /* Ensure reps with no active stock are not shown or handle NULLs */

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Array to store rep data
    $reps = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $reps[] = [
            'id' => $row['rep_id'],
            'name' => $row['rep_name'],
            'contact' => $row['contact_number'],
            'email' => $row['email'],
            'total_products' => $row['total_products'],
            'total_quantity' => $row['total_quantity'],
            'total_amount' => $row['total_amount'],
            'image' => match (intval($row['rep_id']) % 3) {
                0 => 'https://images.unsplash.com/photo-1603893696564-8fbba929f000?ixlib=rb-4.0.3&fit=crop&w=400&q=80',
                1 => 'https://images.unsplash.com/photo-1615485290232-9b8bc7f33909?ixlib=rb-4.0.3&fit=crop&w=400&q=80',
                2 => 'https://images.unsplash.com/photo-1621955961411-7b1f2fbedd00?ixlib=rb-4.0.3&fit=crop&w=400&q=80',
            }
        ];
    }
    ?>

    <?php require_once '../../../header1.php'; ?>
    <main class="page-content">
        <?php if (empty($reps)): ?>
            <p>No representatives found with active stock.</p>
        <?php else: ?>
            <?php foreach ($reps as $rep): ?>
                <div class="card" style="background-image: url('<?php echo htmlspecialchars($rep['image']); ?>');" data-rep-id="<?php echo $rep['id']; ?>">
                    <div class="initial-content">
                        <p class="sales">Total Stock: <?php echo number_format($rep['total_quantity']); ?> units</p>
                    </div>
                    <div class="content">
                        <h2 class="title"><?php echo htmlspecialchars($rep['name']); ?></h2>
                        <p class="copy">Contact: <?php echo htmlspecialchars($rep['contact']); ?></p>
                        <p class="copy">Email: <?php echo htmlspecialchars($rep['email']); ?></p>
                        <p class="sales">Total Products: <?php echo number_format($rep['total_products']); ?></p>
                        <p class="sales">Total Quantity: <?php echo number_format($rep['total_quantity']); ?> units</p>
                        <p class="sales">Total Amount: Rs.<?php echo number_format($rep['total_amount'] ?? 0, 2); ?></p>
                        <button class="btn" onclick="showStockDetails(<?php echo $rep['id']; ?>)">View Stock Details</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>

    <!-- Popup for displaying stock details -->
    <div id="stock-popup" class="popup-overlay">
        <div class="popup-content">
            <span class="popup-close" onclick="closeStockPopup()">&times;</span>
            <h2 id="popup-title">Stock Details</h2>
            <div id="popup-content">
                <p id="loading-message">Loading stock details...</p>
                <div id="stock-details"></div>
            </div>
        </div>
    </div>

    <script>
        // JavaScript fallback for image loading errors
        document.querySelectorAll('.card').forEach(card => {
            const img = new Image();
            img.src = card.style.backgroundImage.slice(5, -2);
            img.onerror = () => {
                console.log(`Failed to load image for ${card.querySelector('.title').textContent}`);
                card.style.backgroundImage = 'url("https://via.placeholder.com/400x450?text=Image+Not+Found")';
            };
        });

        // Add this function to ensure proper behavior on mobile devices
        document.addEventListener('DOMContentLoaded', function() {
            // Check if device supports hover
            const supportsHover = window.matchMedia("(hover: hover)").matches;
            
            if (!supportsHover) {
                // For touch devices, make all content visible by default
                document.querySelectorAll('.content').forEach(content => {
                    content.style.transform = 'translateY(0)';
                });
            }
        });

        function showStockDetails(repId) {
            // Show popup
            document.getElementById('stock-popup').style.display = 'flex';
            document.getElementById('loading-message').style.display = 'block';
            document.getElementById('stock-details').innerHTML = '';

            // Fetch stock details for the rep
            fetch(`get_rep_stock.php?rep_id=${repId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('loading-message').style.display = 'none';
                    
                    if (data.status === 'success') {
                        document.getElementById('popup-title').textContent = `Stock Details for ${data.rep_name}`;
                        
                        // Create table for stock items
                        let tableHTML = `
                            <table class="stock-table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Item Code</th>
                                        <th>Barcode</th>
                                        <th>Quantity</th>
                                        <th>Unit Price (Rs)</th>
                                        <th>Total (Rs)</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;
                        
                        data.items.forEach(item => {
                            // item.total_amount is now pre-calculated as quantity * unit_price from get_rep_stock.php
                            tableHTML += `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td>${item.itemcode || 'N/A'}</td>
                                    <td>${item.barcode || 'N/A'}</td>
                                    <td>${item.quantity}</td>
                                    <td>${parseFloat(item.unit_price).toFixed(2)}</td>
                                    <td>${parseFloat(item.total_amount).toFixed(2)}</td> 
                                    <td>${item.status}</td>
                                </tr>
                            `;
                        });
                        
                        tableHTML += `
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3">Total</th>
                                        <th>${data.summary.total_quantity}</th>
                                        <th></th>
                                        <th>${parseFloat(data.summary.total_amount).toFixed(2)}</th> 
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        `;
                        
                        document.getElementById('stock-details').innerHTML = tableHTML;
                    } else {
                        document.getElementById('stock-details').innerHTML = `<p>Error: ${data.message}</p>`;
                    }
                })
                .catch(error => {
                    document.getElementById('loading-message').style.display = 'none';
                    document.getElementById('stock-details').innerHTML = `<p>Error loading data: ${error.message}</p>`;
                    console.error('Error:', error);
                });
        }

        function closeStockPopup() {
            document.getElementById('stock-popup').style.display = 'none';
        }

        // Close popup when clicking outside the content
        document.getElementById('stock-popup').addEventListener('click', function(event) {
            if (event.target === this) {
                closeStockPopup();
            }
        });
    </script>
</body>

</html>