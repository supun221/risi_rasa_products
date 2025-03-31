<!-- non_moving_products.php -->
<!DOCTYPE html>
<html>

<head>
    <title>Non-Moving Products</title>
    <link rel="stylesheet" href="../../assets/css/section_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container">
        <h2>Non-Moving Products</h2>

        <div class="filters">
            <input type="date" id="fromDate" placeholder="From Date">
            <input type="date" id="toDate" placeholder="To Date">
            <select id="topCount">
                <option value="10">Top 10</option>
                <option value="20">Top 20</option>
                <option value="50">Top 50</option>
                <option value="100">Top 100</option>
            </select>
            <button onclick="fetchNonMovingProducts()">Search</button>
        </div>

        <div class="table-container">
            <table id="results">
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
        function fetchNonMovingProducts() {
            const filters = {
                from_date: $('#fromDate').val(),
                to_date: $('#toDate').val(),
                top_count: $('#topCount').val()
            };

            $('#results tbody').html('<tr><td colspan="4">Loading...</td></tr>');

            $.ajax({
                url: 'fetch_non_moving_products.php',
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
                        <td>${item.available_stock || 'N/A'}</td>
                        <td>${item.last_sold_date || 'Never Sold'}</td>
                    </tr>`
                );
            });
        }

        $(document).ready(function () {
            fetchNonMovingProducts();
        });
    </script>
</body>

</html>
