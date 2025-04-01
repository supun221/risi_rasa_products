<!DOCTYPE html>
<html>

<head>
    <title>Expire Notification</title>
    <link rel="stylesheet" href="../../assets/css/section_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <style>
        .filters {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            /* Space between input fields */
            margin: 20px 0;
        }

        .filters input[type="text"],
        .filters input[type="date"] {
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            width: 200px;
            /* Adjust width for uniformity */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .filters button {
            padding: 10px 20px;
            background-color: #f7c4c4; /* Theme button background color */
            color: #483535; /* Theme button text color */
            border: 1px solid #e9a8a8; /* Theme border color */
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .filters button:hover {
            background-color: #e9a8a8; /* Theme button hover color */
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            text-align: center;
        }

        h2 {
            text-align: center;
            font-size: 20px;
            margin-top: 1px;
            /* margin-bottom: -501px; */
        }

        .table-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 98%;
            /* Ensures the container spans the full width */
            margin: 0 auto;
            /* Centers the container itself */
        }

        table {
            width: 98%;
            /* This might need to be reconsidered for proper table layout */
            border-collapse: collapse;
            margin: 5px 0;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.8);
            /* Adds shadow effect */
        }

        table th,
        table td {
            padding: 5px 5px;
            text-align: left;
            border: 1px solid #ddd;
        }

        .expired td {
            background-color: #ffb3b3 !important;
            /* Light red for expired items */
            /* color: black !important; */
            /* font-weight: bold; */
        }

        table th {
            background-color: #e05b5b; /* Changed to match theme's primary color */
            border: none;
            color: white;
        }

        table tr:hover {
            background-color: #f1f1f1;
            /* Row hover effect */
        }
    </style>
    <div class="container">
        <h2>Expire Notification</h2>

        <!-- Search and Date Filters -->
        <div class="filters">
            <input
                type="text"
                id="search"
                placeholder="Search by Product Name or Barcode"
                onkeyup="applyFilters()">
            <input
                type="date"
                id="fromDate"
                placeholder="From Date">
            <input
                type="date"
                id="toDate"
                placeholder="To Date">
            <button onclick="applyFilters()">Search</button>
        </div>

        <!-- Results Table -->
        <table id="results">
            <thead>
                <tr>
                    <th>Barcode</th>
                    <th>Product Name</th>
                    <th>Available Quantity</th>
                    <th>Supplier Name</th>
                    <th>Expire Date</th>
                </tr>
            </thead>
            <tbody>
                <!-- Stock data will be dynamically added here -->
            </tbody>
        </table>
    </div>

    <script>
        // Fetch and populate table
        function fetchData(filters = {}) {
            $('#results tbody').html('<tr><td colspan="5">Loading...</td></tr>'); // Loading indicator

            $.ajax({
                url: 'expire_alerts.php', // Backend script
                method: 'GET',
                dataType: 'json',
                data: filters, // Filters passed as query parameters
                success: function(data) {
                    populateTable(data);
                },
                error: function() {
                    $('#results tbody').html('<tr><td colspan="5">Error fetching data</td></tr>');
                }
            });
        }

        // Populate table with fetched data
        // Populate table with fetched data
        function populateTable(data) {
            $('#results tbody').empty(); // Clear previous results

            if (data.length === 0) {
                $('#results tbody').html('<tr><td colspan="5">No records found</td></tr>');
                return;
            }

            $.each(data, function(index, item) {
                // Check if item is expired
                const isExpired = new Date(item.expire_date) < new Date();
                const rowClass = isExpired ? 'class="expired"' : '';

                $('#results tbody').append(
                    `<tr ${rowClass}>
                <td>${item.barcode || 'N/A'}</td>
                <td>${item.product_name || 'N/A'}</td>
                <td>${item.available_stock || 'N/A'}</td>
                <td>${item.supplier_name || 'N/A'}</td>
                <td>${item.expire_date || 'N/A'}</td>
            </tr>`
                );
            });
        }


        // Apply filters based on user input
        function applyFilters() {
            const filters = {
                search: $('#search').val(), // Get search term
                from_date: $('#fromDate').val(), // Get from date
                to_date: $('#toDate').val() // Get to date
            };
            fetchData(filters);
        }

        // Initial load of all data
        $(document).ready(function() {
            applyFilters(); // Fetch all data on page load
        });
    </script>
</body>

</html>