<?php
// return_form.php

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include the database configuration
require_once '../../../config/databade.php';

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch product details from the database
function fetchProductDetails($barcode, $conn)
{
    $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["return_items"]) && isset($_POST["supplier_id"])) {
        $returnItems = json_decode($_POST["return_items"], true);
        $supplierId = intval($_POST["supplier_id"]);

        // Calculate total amount
        $totalAmount = array_reduce($returnItems, function ($sum, $item) {
            return $sum + $item['total'];
        }, 0);

        // Insert into returns table
        $stmt = $conn->prepare("INSERT INTO returns (total_amount, supplier_id) VALUES (?, ?)");
        $stmt->bind_param("di", $totalAmount, $supplierId);
        $stmt->execute();
        $returnId = $stmt->insert_id;
        $stmt->close();

        // Insert into return_items table
        foreach ($returnItems as $item) {
            $stmt = $conn->prepare("INSERT INTO return_items (return_id, barcode, product_name, quantity, our_price, max_retail_price, total) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issiddd", $returnId, $item['barcode'], $item['productName'], $item['quantity'], $item['ourPrice'], $item['MRPprice'], $item['total']);
            $stmt->execute();
            $stmt->close();
            // Reduce available_stock in stock_entries
            $stmt = $conn->prepare("UPDATE stock_entries SET available_stock = available_stock - ? WHERE barcode = ?");
            $stmt->bind_param("is", $item['quantity'], $item['barcode']);
            $stmt->execute();
            $stmt->close();
        }

        // Return success response
        echo json_encode(["success" => true, "message" => "Return submitted successfully.", "returnId" => $returnId]);
        exit;
        
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Form</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        .barcode-reader-container {
            margin-top: 20px;
        }

        .barcode-reader-table {
            width: 100%;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        #supplier {
            width: 20%;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: border-color 0.3s ease;


        }

        .barcode-reader-table th,
        .barcode-reader-table td {
            padding: 10px;
            text-align: center;
        }

        .show-mis-modal {
            display: block !important;
        }

        .selected-row {
            background-color: #2980b9 !important;
            color: white !important;
        }

        .thead-dar1 {
            background-color: #2980b9;
            border-radius: 4px;
        }

        .total-amount {
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
        }

        .barcode-reader-container {
            margin-top: 20px;
        }

        .barcode-reader-table {
            width: 100%;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 25px;

        }

        #supplier {
            width: 20%;
            margin-bottom: 10px;
            border-radius: 5px;
            transition: border-color 0.3s ease;


        }

        .barcode-reader-table th,
        .barcode-reader-table td {
            padding: 10px;
            text-align: center;
        }

        .show-mis-modal {
            display: block !important;
        }

        .selected-row {
            background-color: #2980b9 !important;
            color: white !important;
        }

        .thead-dar1 {
            background-color: #2980b9;
            border-radius: 4px;
        }

        .total-amount {
            font-size: 1.5em;
            font-weight: bold;
            margin-top: 20px;
        }


        /* General form container styling */
        .container {
            max-width: 800px;
            margin: 0 auto;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-family: Arial, sans-serif;
        }

        /* Form heading */
        .container h1 {
            text-align: center;
            font-size: 24px;
            color: #343a40;
            margin-bottom: 20px;
        }

        /* Form group styling */
        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #495057;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            font-size: 14px;
            color: #495057;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .thead-dark1 {
            background-color: #0a3981;
            color: #ffffff;
            text-align: left;
            padding: 10px;
            font-size: 14px;
        }

        /* Table styling */
        .table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .table thead th {
            background-color: #0a3981;
            color: #ffffff;
            text-align: left;
            padding: 10px;
            font-size: 14px;
        }

        .table tbody td {
            padding: 10px;
            border: 1px solid #dee2e6;
        }

        .barcode-input,
        .barcode-quantity,
        .barcode-product-name,
        .barcode-MRP-price,
        .barcode-our-price {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ced4da;
            border-radius: 5px;
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        /* Button styling */
        button {
            background-color: #0056b3;
            color: #fff;
            font-size: 14px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s ease;
        }

        .btn2 {
            background-color: #0056b3;
            color: #fff;
            font-size: 14px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: background-color 0.2s ease; 
        }

        button:hover {
            background-color: #0056b3;
        }

        button:disabled {
            background-color: #ced4da;
            cursor: not-allowed;
        }

        .btn-secondary {
            background-color: #6c757d;
            margin-left: 10px;
        }

        .btn-secondary:hover {
            background-color: #0a3981;
        }

        /* Total amount styling */
        .total-amount {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            text-align: right;
        }

        /* Box style for tables */
        .barcode-reader-container,
        #return-items-tb {
            background-color: #ffffff;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 15px;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            table {
                font-size: 12px;
            }

            button {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Return Form</h1>
        <div class="form-group">
            <label for="supplier">Supplier:</label>
            <select id="supplier" name="supplier" class="form-control">
                <option value="">Select Supplier</option>
                <?php
                // Fetch suppliers from the database
                $suppliers = [];
                $result = $conn->query("SELECT supplier_id, supplier_name FROM suppliers");
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='{$row['supplier_id']}'>{$row['supplier_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="barcode-reader-container">
            <table class="table table-striped barcode-reader-table" id="barcode-reader-tb">
                <thead class="thead-dark1">
                    <tr>
                        <th>Barcode</th>
                        <th>Product Name</th>
                        <th>Quantity</th>
                        <th>Cost Price</th>
                        <th>MR Price</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="barcode-reader-items">
                    <tr>
                        <td>
                            <input type="text" id="barcode-input" class="barcode-input" placeholder="Enter barcode">
                        </td>
                        <td>
                            <input type="text" id="product-name" class="barcode-product-name" disabled>
                        </td>
                        <td>
                            <input type="number" id="quantity" class="barcode-quantity" value="1" min="1">
                        </td>
                        <td>
                            <input type="text" id="our-price" class="barcode-our-price" disabled>
                        </td>
                        <td>
                            <input type="text" id="MRP-price" class="barcode-MRP-price" disabled>
                        </td>
                        <td>
                            <button onclick="addToReturnTable()">Add </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h2>Return Items</h2>
        <table class="table table-striped" id="return-items-tb">
            <thead class="thead-dark1">
                <tr>
                    <th>Barcode</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Cost Price</th>
                    <th>MRP</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="return-items">
                <!-- Return items will be added here -->
            </tbody>
        </table>

        <div class="total-amount">
            Total Amount: <span id="total-amount">0.00</span>
        </div>

        <button class="btn2" onclick="submitReturn()">Submit Return</button>
        <!-- <button class="btn btn-secondary" onclick="printBill()">Print Bill</button> -->
    </div>

    <!-- Modal for multiple product selection -->
    <div id="mis-modal" class="modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Product</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="hideMisModal()">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped" id="mis-table">
                        <thead>
                            <tr>
                                <th>Stock ID</th>
                                <th>Product Name</th>
                                <th>Barcode</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Product rows will be added here dynamically -->
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="hideMisModal()">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let returnItems = [];
        let currentProduct = null;
        let currentRowIndex = 0;

        function addToReturnTable() {
            const barcode = document.getElementById("barcode-input").value.trim();
            const productName = document.getElementById("product-name").value;
            const quantity = parseInt(document.getElementById("quantity").value) || 1;
            const ourPrice = parseFloat(document.getElementById("our-price").value) || 0;
            const MRPprice = parseFloat(document.getElementById("MRP-price").value) || 0;

            if (!barcode || !productName) {
                alert("Please enter a valid barcode and fetch product details.");
                return;
            }

            const total = quantity * ourPrice;
            const item = {
                barcode,
                productName,
                quantity,
                ourPrice,
                MRPprice,
                total
            };

            returnItems.push(item);
            updateReturnTable();
            clearInputFields();
            updateTotalAmount();
        }

        function updateReturnTable() {
            const tbody = document.getElementById("return-items");
            tbody.innerHTML = "";

            returnItems.forEach((item, index) => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${item.barcode}</td>
                    <td>${item.productName}</td>
                    <td>${item.quantity}</td>
                    <td>${item.ourPrice.toFixed(2)}</td>
                    <td>${item.MRPprice.toFixed(2)}</td>
                    <td>${item.total.toFixed(2)}</td>
                    <td><button onclick="removeReturnItem(${index})">Remove</button></td>
                `;
                tbody.appendChild(row);
            });
        }

        function removeReturnItem(index) {
            returnItems.splice(index, 1);
            updateReturnTable();
            updateTotalAmount();
        }

        function clearInputFields() {
            document.getElementById("barcode-input").value = "";
            document.getElementById("product-name").value = "";
            document.getElementById("quantity").value = "1";
            document.getElementById("our-price").value = "";
            document.getElementById("MRP-price").value = "";
        }

        function updateTotalAmount() {
            const totalAmount = returnItems.reduce((sum, item) => sum + item.total, 0);
            document.getElementById("total-amount").textContent = totalAmount.toFixed(2);
        }

        let lastReturnItems = []; // Store the last return items for printing

        let lastReturnId = null; // Global variable to store the last return ID

        function submitReturn() {
            if (returnItems.length === 0) {
                alert("No items to return.");
                return;
            }

            const supplierId = document.getElementById("supplier").value;
            if (!supplierId) {
                alert("Please select a supplier.");
                return;
            }

            fetch("return_form.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `return_items=${encodeURIComponent(JSON.stringify(returnItems))}&supplier_id=${supplierId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        lastReturnItems = [...returnItems]; // Store items before clearing
                        lastReturnId = data.returnId; // Store the returnId from the server

                        Swal.fire({
                            title: 'Success!',
                            text: 'Return submitted successfully.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                Swal.fire({
                                    title: 'Print Bill?',
                                    text: 'Do you want to print the bill?',
                                    icon: 'question',
                                    showCancelButton: true,
                                    confirmButtonText: 'Yes, print it!',
                                    cancelButtonText: 'No, thanks'
                                }).then((printResult) => {
                                    if (printResult.isConfirmed) {
                                        printBill(); // Call printBill AFTER confirmation
                                    }
                                });
                            }
                        });

                        returnItems = []; // Clear items AFTER storing them
                        updateReturnTable();
                        updateTotalAmount();
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Error submitting return.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error("Error submitting return:", error);
                    Swal.fire({
                        title: 'Error!',
                        text: 'An error occurred while submitting the return.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }

        function printBill() {
            if (lastReturnItems.length === 0) { // Use lastReturnItems instead of returnItems
                alert("No items to print.");
                return;
            }

            // Use the returnId from the last submitted return
            const returnId = lastReturnId; // This should be set after a successful return submission
            const currentDate = new Date().toLocaleDateString();
            const supplierId = document.getElementById("supplier").value;
            const supplierName = document.getElementById("supplier").options[document.getElementById("supplier").selectedIndex].text;

            const productList = lastReturnItems.map(item => ({
                productName: item.productName,
                unitPrice: item.ourPrice,
                quantity: item.quantity,
                MRPprice: item.MRPprice, // Added MRP
                subtotal: item.total
            }));

            const totalAmount = lastReturnItems.reduce((sum, item) => sum + item.total, 0);

            const payload = {
                returnId,
                currentDate,
                productList,
                totalAmount,
                supplierId,
                supplierName
            };

            fetch("return_bill.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.blob())
                .then(blob => {
                    const url = window.URL.createObjectURL(blob);
                    const newWindow = window.open(url, '_blank');

                    if (newWindow) {
                        newWindow.onload = () => {
                            newWindow.focus();
                            newWindow.print();
                        };
                    } else {
                        Swal.fire({
                            title: "Popup Blocked!",
                            title: "Popup Blocked!",
                            text: "Please allow popups and try again.",
                            icon: "warning",
                            confirmButtonText: "OK"
                        });
                    }
                })
                .catch(error => {
                    console.error("Error generating bill:", error);
                    Swal.fire({
                        title: "Error!",
                        text: "An error occurred while generating the bill.",
                        icon: "error",
                        confirmButtonText: "OK"
                    });
                });
        }



        // Fetch product details and handle multiple products
        document.getElementById("barcode-input").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                const barcode = this.value.trim();
                if (barcode) {
                    fetchProductDetails(barcode);
                }
            }
        });

        function fetchProductDetails(barcode) {
            fetch("fetch_product_static.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `barcode=${encodeURIComponent(barcode)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.products && data.products.length > 0) {
                        if (data.products.length === 1) {
                            // Single product found
                            const product = data.products[0];
                            document.getElementById("product-name").value = product.product_name;
                            document.getElementById("our-price").value = parseFloat(product.cost_price).toFixed(2);
                            document.getElementById("MRP-price").value = parseFloat(product.max_retail_price).toFixed(2);
                            currentProduct = product;
                        } else {
                            // Multiple products found
                            showMisModal(data.products);
                        }
                    } else {
                        alert("Product not found!");
                    }
                })
                .catch(error => {
                    console.error("Error fetching product details:", error);
                });
        }

        // Show modal for multiple product selection
        function showMisModal(products) {
            const misModal = document.getElementById("mis-modal");
            const misTableBody = document.querySelector("#mis-table tbody");

            misTableBody.innerHTML = "";
            products.forEach((product, index) => {
                const row = document.createElement("tr");
                if (index === 0) {
                    row.classList.add("selected-row");
                }
                row.dataset.productId = product.id;
                row.dataset.productDetails = JSON.stringify(product);
                row.innerHTML = `
                    <td>${product.stock_id}</td>
                    <td>${product.product_name}</td>
                    <td>${product.barcode}</td>
                    <td>${parseFloat(product.cost_price).toFixed(2)}</td>
                `;
                misTableBody.appendChild(row);
            });

            misModal.classList.add("show-mis-modal");
            misModal.setAttribute("tabindex", "-1");
            misModal.focus();

            // Add keyboard navigation
            document.addEventListener("keydown", handleArrowKeyNavigation);
        }

        // Hide modal
        function hideMisModal() {
            const misModal = document.getElementById("mis-modal");
            misModal.classList.remove("show-mis-modal");
            document.removeEventListener("keydown", handleArrowKeyNavigation);
        }

        // Handle arrow key navigation in modal
        function handleArrowKeyNavigation(e) {
            const rows = document.querySelectorAll("#mis-table tbody tr");
            if (!rows.length) return;

            if (e.key === "ArrowDown" || e.key === "ArrowUp") {
                e.preventDefault();
            } else {
                return;
            }

            rows[currentRowIndex].classList.remove("selected-row");
            if (e.key === "ArrowDown") {
                currentRowIndex = (currentRowIndex + 1) % rows.length; // Wrap to the top
            } else if (e.key === "ArrowUp") {
                currentRowIndex = (currentRowIndex - 1 + rows.length) % rows.length; // Wrap to the bottom
            }
            rows[currentRowIndex].classList.add("selected-row");
            rows[currentRowIndex].scrollIntoView({
                block: "nearest"
            });
        }

        // Handle End key to select product from modal
        document.addEventListener("keydown", (e) => {
            if (e.key === "End") {
                const misModal = document.getElementById("mis-modal");
                if (misModal.classList.contains("show-mis-modal")) {
                    const rows = document.querySelectorAll("#mis-table tbody tr");
                    if (rows.length > 0) {
                        const selectedRow = rows[currentRowIndex];
                        const productDetails = JSON.parse(selectedRow.dataset.productDetails);
                        currentProduct = productDetails;

                        // Populate input fields with selected product details
                        document.getElementById("product-name").value = productDetails.product_name;
                        document.getElementById("our-price").value = parseFloat(productDetails.cost_price).toFixed(2);
                        document.getElementById("MRP-price").value = parseFloat(productDetails.max_retail_price).toFixed(2);
                        const quantityInput = document.getElementById("quantity");
                        // Allow the user to enter a custom quantity
                        quantityInput.focus();
                        quantityInput.select();

                        // Hide the modal after selecting the product
                        hideMisModal();
                    }
                }
            }
        });
    </script>
</body>

</html>
