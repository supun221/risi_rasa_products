<!DOCTYPE html>
<html>

<head>
    <title>Low Stock Alerts</title>
    <link rel="stylesheet" href="../../assets/css/section_style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <h2>Low Stock Alerts</h2>
    <style>
        #search1 {
            width: 40%;
            max-width: 400px;
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        #search1:focus {
            border-color: hsl(229, 98%, 81%);
            outline: none;
            box-shadow: 0 0 5px hsl(229, 98%, 81%);
        }

        #categoryFilter {
            width: 40%;
            max-width: 300px;
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
    </style>
    <!-- Search Form -->
    <input
        type="text"
        id="search1"
        placeholder="Search by Barcode or Product Name"
        onkeyup="liveSearch()">

    <!-- Category Filter Dropdown -->
    <select id="categoryFilter" onchange="liveSearch()">
        <option value="">All Categories</option>
        <!-- Options will be dynamically inserted here -->
    </select>

    <table id="resultsTable">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Low Stock</th>
                <th>Barcode</th>
                <th>Supplier Name</th>
            </tr>
        </thead>
        <tbody>
            <!-- Results will be dynamically inserted here -->
        </tbody>
    </table>

    <script>
        function liveSearch() {
            const query = document.getElementById('search1').value;
            const category = document.getElementById('categoryFilter').value;

            // Send AJAX request with or without a search query and category filter
            $.ajax({
                url: 'low_stock_ajax.php',
                method: 'GET',
                data: {
                    search: query,
                    category: category
                },
                success: function(data) {
                    const tableBody = $('#resultsTable tbody');
                    tableBody.empty();

                    if (data.length > 0) {
                        data.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${item.product_name}</td>
                                    <td>${item.available_stock}</td>
                                    <td>${item.barcode}</td>
                                    <td>${item.supplier_name}</td>
                                </tr>`;
                            tableBody.append(row);
                        });
                    } else {
                        tableBody.append('<tr><td colspan="4">No results found</td></tr>');
                    }
                },
                error: function() {
                    alert('An error occurred while fetching data.');
                }
            });
        }

        // Function to load categories into the dropdown
        function loadCategories() {
            $.ajax({
                url: 'get_categories.php',
                method: 'GET',
                success: function(data) {
                    const categoryFilter = $('#categoryFilter');
                    categoryFilter.empty();
                    categoryFilter.append('<option value="">All Categories</option>');

                    if (data.length > 0) {
                        data.forEach(category => {
                            categoryFilter.append(`<option value="${category}">${category}</option>`);
                        });
                    }
                },
                error: function() {
                    alert('An error occurred while fetching categories.');
                }
            });
        }

        // Initial load of all data and categories
        $(document).ready(function() {
            liveSearch();
            loadCategories();
        });
    </script>
</body>

</html>