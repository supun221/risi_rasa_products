<!DOCTYPE html>
<html>

<head>
    <title>Fast & Non-Moving Products</title>
    <link rel="stylesheet" href="../../assets/css/section_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            text-align: center;
        }

        .filters {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin: 20px 0;
        }

        .filters input,
        .filters select,
        .filters button {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .filters button {
            background-color: hsl(229, 98%, 81%);
            color: white;
            cursor: pointer;
        }

        .filters button:hover {
            background-color: hsl(229, 98%, 71%);
        }

        .table-container {
            width: 98%;
            margin: 0 auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
        }

        table th,
        table td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: hsl(229, 98%, 81%);
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }

        h2 {
            margin-bottom: 20px;
        }
    </style>

    <div class="container">
        <h2>Fast & Non-Moving Products</h2>

        <!-- Filters for Fast Moving Products -->
        <div class="filters">
            <input type="date" id="fromDateFast" placeholder="From Date">
            <input type="date" id="toDateFast" placeholder="To Date">
            <select id="topCountFast">
                <option value="10">Top 10</option>
                <option value="20">Top 20</option>
                <option value="50">Top 50</option>
                <option value="100">Top 100</option>
            </select>
            <button onclick="fetchFastMovingProducts()">Search Fast Moving</button>
        </div>

        <!-- Table for Fast Moving Products -->
        <div class="table-container">
            <h3>Fast Moving Products</h3>
            <table id="fast-moving-results">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>Product Name</th>
                        <th>Total Sold</th>
                        <th>Last Purchased Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically inserted here -->
                </tbody>
            </table>
        </div>

        <!-- Filters for Non-Moving Products -->
        <div class="filters">
            <input type="date" id="fromDateNon" placeholder="From Date">
            <input type="date" id="toDateNon" placeholder="To Date">
            <select id="topCountNon">
                <option value="10">Top 10</option>
                <option value="20">Top 20</option>
                <option value="50">Top 50</option>
                <option value="100">Top 100</option>
            </select>
            <button onclick="fetchNonMovingProducts()">Search Non-Moving</button>
        </div>

        <!-- Table for Non-Moving Products -->
        <div class="table-container">
            <h3>Non-Moving Products</h3>
            <table id="non-moving-results">
                <thead>
                    <tr>
                        <th>Barcode</th>
                        <th>Product Name</th>
                        <th>Available Stock</th>
                        <th>Last Sold Date</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Data will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Fetch Fast Moving Products
        function fetchFastMovingProducts() {
            const filters = {
                from_date: $('#fromDateFast').val(),
                to_date: $('#toDateFast').val(),
                top_count: $('#topCountFast').val()
            };

            $('#fast-moving-results tbody').html('<tr><td colspan="4">Loading...</td></tr>');

            $.ajax({
                url: 'fetch_fast_moving_products.php',
                method: 'GET',
                dataType: 'json',
                data: filters,
                success: function(data) {
                    populateFastMovingTable(data);
                },
                error: function() {
                    $('#fast-moving-results tbody').html('<tr><td colspan="4">Error fetching data</td></tr>');
                }
            });
        }

        // Populate Fast Moving Products Table
        function populateFastMovingTable(data) {
            $('#fast-moving-results tbody').empty();

            if (data.length === 0) {
                $('#fast-moving-results tbody').html('<tr><td colspan="4">No records found</td></tr>');
                return;
            }

            $.each(data, function(index, item) {
                $('#fast-moving-results tbody').append(
                    `<tr>
                        <td>${item.product_barcode || 'N/A'}</td>
                        <td>${item.product_name || 'N/A'}</td>
                        <td>${item.total_sold || 'N/A'}</td>
                        <td>${item.last_purchased_date || 'N/A'}</td>
                    </tr>`
                );
            });
        }

        // Fetch Non-Moving Products
        function fetchNonMovingProducts() {
            const filters = {
                from_date: $('#fromDateNon').val(),
                to_date: $('#toDateNon').val(),
                top_count: $('#topCountNon').val()
            };

            $('#non-moving-results tbody').html('<tr><td colspan="4">Loading...</td></tr>');

            $.ajax({
                url: 'fetch_non_moving_products.php',
                method: 'GET',
                dataType: 'json',
                data: filters,
                success: function(data) {
                    populateNonMovingTable(data);
                },
                error: function() {
                    $('#non-moving-results tbody').html('<tr><td colspan="4">Error fetching data</td></tr>');
                }
            });
        }

        // Populate Non-Moving Products Table
        function populateNonMovingTable(data) {
            $('#non-moving-results tbody').empty();

            if (data.length === 0) {
                $('#non-moving-results tbody').html('<tr><td colspan="4">No records found</td></tr>');
                return;
            }

            $.each(data, function(index, item) {
                $('#non-moving-results tbody').append(
                    `<tr>
                        <td>${item.product_barcode || 'N/A'}</td>
                        <td>${item.product_name || 'N/A'}</td>
                        <td>${item.available_stock || 'N/A'}</td>
                        <td>${item.last_sold_date || 'Never Sold'}</td>
                    </tr>`
                );
            });
        }

        // Load data on page load
        $(document).ready(function() {
            fetchFastMovingProducts();
            fetchNonMovingProducts();
        });
    </script>
</body>

</html>