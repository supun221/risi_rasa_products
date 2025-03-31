<!DOCTYPE html>
<html>

<head>
    <title>Fast Moving Products</title>
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
            gap: 10px;
            margin: 20px 0;
        }

        .filters input, .filters select, .filters button {
            padding: 10px;
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

        table th, table td {
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
    </style>

    <div class="container">
        <h2>Fast Moving Products</h2>

        <div class="filters">
            <input type="date" id="fromDate" placeholder="From Date">
            <input type="date" id="toDate" placeholder="To Date">
            <select id="topCount">
                <option value="10">Top 10</option>
                <option value="20">Top 20</option>
                <option value="50">Top 50</option>
                <option value="100">Top 100</option>
            </select>
            <button onclick="fetchFastMovingProducts()">Search</button>
        </div>

        <div class="table-container">
            <table id="results">
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
    </div>

    <script>
        function fetchFastMovingProducts() {
            const filters = {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val(),
                top_count: $('#topCount').val()
            };

            $('#results tbody').html('<tr><td colspan="4">Loading...</td></tr>');

            $.ajax({
                url: 'fetch_fast_moving_products.php',
                method: 'GET',
                dataType: 'json',
                data: filters,
                success: function (data) {
                    populateTable(data);
                },
                error: function () {
                    $('#results tbody').html('<tr><td colspan="4">Error fetching data</td></tr>');
                }
            });
        }

        function populateTable(data) {
            $('#results tbody').empty();

            if (data.length === 0) {
                $('#results tbody').html('<tr><td colspan="4">No records found</td></tr>');
                return;
            }

            $.each(data, function (index, item) {
                $('#results tbody').append(
                    `<tr>
                        <td>${item.product_barcode || 'N/A'}</td>
                        <td>${item.product_name || 'N/A'}</td>
                        <td>${item.total_sold || 'N/A'}</td>
                        <td>${item.last_purchased_date || 'N/A'}</td>
                    </tr>`
                );
            });
        }

        $(document).ready(function () {
            fetchFastMovingProducts();
        });
    </script>
</body>

</html>


