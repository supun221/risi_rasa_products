<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Invoice</title>
    <link rel="stylesheet" href="../../assets/css/invoice_style.css">
</head>


<body>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const tableBody = document.getElementById("hold-invoice-rows");
            for (let i = 0; i < 8; i++) {
                const row = document.createElement("tr");
                const cell = document.createElement("td");
                cell.textContent = "1";
                row.appendChild(cell);
                tableBody.appendChild(row);
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            // Update immediately when the page loads
            updateDateTime();

            // Update every second
            setInterval(updateDateTime, 1000);
        });

        function updateDateTime() {
            const now = new Date();

            // Update time
            const timeElement = document.getElementById('current-time');
            timeElement.textContent = now.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            // Update date
            const dateElement = document.getElementById('current-date');
            dateElement.textContent = now.toLocaleDateString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
        }

        // Ensure the script runs after the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', () => {
            // Update immediately when page loads
            updateDateTime();

            // Update every second
            setInterval(updateDateTime, 1000);
        });

    </script>
    <div class="invoice-container">

        <!-- Header Section -->
        <div class="header-container">
            <!-- Left Section: Inventory Image -->
            <div class="inventory-section">
                <h1 class="invoice-title">Create Invoice:</h1>
                <div class="inventory-image">
                    <img src="../../assets/images/cart.png" alt="Inventory">
                </div>
            </div>

            <!-- Middle Section: Company Name -->
            <div class="company-name">
                <h2>එග්ලන්ඩ් සුපර්</h2>
                <h1>Eggland Super</h1>
                <p>හොඳම දේ අඩුම මිලට</p>
            </div>
        </div>


        </header>
        <div class="invoice-header">
            <p>Total Amount: 0.00</p>
            <p>Cash Tendered: 0.00</p>
            <p>Balance: 0.00</p>
            <div class="invoice-bill">
                <h2>Next Bill No:</h2>
            </div>

        </div>
        <div id="content1">
            <p>image</p>
            <p>qty: 0.00</p>
            <p>mrp: 0.00</p>
            <div class="invoice-bill">
                <!-- <h2>Next Bill No:</h2> -->
            </div>

        </div>
        <!-- <div class="current-product-section">
            <h3>Current Product</h3>
            <input type="text" id="barcode-input" placeholder="Scan or enter barcode" />
            <div id="current-product" class="product-display">
                <p>Scan a barcode to display product details.</p>
            </div>
        </div> -->
    </div>






    </div>
    <div class="content">
        <div class="salesman-section">
            <!-- <button>Select Salesman</button> -->
            <div class="salesmen-add-button">
                <button type="button" onclick="location.href='../salesman/add_salesman.php';">
                    <span class="icon">+</span>
                </button>
                          
            </div>
            <p>Salesman</p>

            <select name="salesman" required>
                <option value="" disabled selected>Select Salesman</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>

            <p>User</p>
            <select name="user" required>
                <option value="" disabled selected>Select User</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>

            <p>Location</p>
            <select name="store" required>
                <option value="" disabled selected>Select Store Type</option>
                <option value="Main Store">Main</option>
                <option value="Branch Store">Branch</option>
                <option value="Kurunegala Store">Kurunegala</option>
            </select>

            <p>Date</p>
            <div class="date">
                <p><strong id="current-date">November 21, 2024</strong></p>
            </div>
            <div class="time">
                <h2 id="current-time">7:28:50 AM</h2>
            </div>
        </div>

        <div class="customer-section">
            <div class="salesmen-add-button">
                <button type="button">
                    <span class="icon">+</span>
                </button>
                          
            </div>
            <p>Select Customer</p>

            <select name="Customer" required>
                <option value="" disabled selected>Select Customer</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
            </select>

            <div class="customer-type">
                <label>
                    <input type="radio" name="customerType" value="Whole Sale" required>
                    Whole Sale
                </label>
                <label>
                    <input type="radio" name="customerType" value="Retail">
                    Retail
                </label>
            </div>

            <button>Credit Limit</button>
            <button>Create New Invoice</button>
        </div>
    </div>
    <div class="invoice-table">
        <div class="radio-buttons-container">
            <label>
                <input type="radio" name="options" value="Invoice" />
                Invoice
            </label>
            <label>
                <input type="radio" name="options" value="Quotation" />
                Quotation
            </label>
            <label>
                <input type="radio" name="options" value="Sales Order" />
                Sales Order
            </label>
            <label>
                <input type="radio" name="options" value="Refund" />
                Refund
            </label>
            <label>
                <input type="radio" name="options" value="Return" />
                Return
            </label>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Stock ID</th>
                    <th>Scan Barcode</th>
                    <th>Product Information</th>
                    <th>Price</th>
                    <th>QTY</th>
                    <th>Discount %</th>
                    <th>Free</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>00074</td>
                    <td>4792192936002</td>
                    <td>4GB Serious Bar 10g 40/=</td>
                    <td>35</td>
                    <td>2</td>
                    <td>0</td>
                    <td>0</td>
                    <td>70</td>
                </tr>
            </tbody>
        </table>
    </div>
    </div>


    <div class="sidebar">
        <!-- Table -->
        <div class="table-container">
            <table class="table-striped">
                <thead>
                    <tr>
                        <th> <button>Hold Invoice</button></th>

                    </tr>
                </thead>
                <tbody id="hold-invoice-rows">
                    <!-- Rows will be dynamically added here -->
                </tbody>
            </table>
        </div>

    </div>
    <div class="button-bar">
        <div class="right-side-buttons">
            <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                <img src="../../assets/images/evening.png" alt="Log Out">
                <span>Day End</span>
            </button>
            <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                <img src="../../assets/images/power.png" alt="Log Out">
                <span>Log Out</span>
            </button>
            <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                <img src="../../assets/images/money.png" alt="Log Out">
                <span>Payment</span>
            </button>
            <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                <img src="../../assets/images/stock.png" alt="Log Out">
                <span>Stock</span>
            </button>
            <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                <img src="../../assets/images/delete.png" alt="Log Out">
                <span>Delete</span>
            </button>
        </div>
        <div class="bottom-panel">
            <div class="bill1">

                <select name="bill1" required>
                    <option value="" disabled selected>selct bills</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                </select>

            </div>
            <div class="bill2">
                <p class="summary-text">No Item:</p>
                <p class="summary-text">Total Qty:</p>
                <p class="summary-text">Total Weight:</p>
            </div>
            <div class="function-buttons">

                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <img src="../../assets/images/excel.png" alt="Log Out">
                    <span>Export</span>
                </button>
                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <img src="../../assets/images/cal.png" alt="Log Out">
                    <span>Calculator</span>
                </button>
                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <img src="../../assets/images/cardpay.png" alt="Log Out">
                    <span>Card Pyment</span>
                </button>
                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <img src="../../assets/images/attendenace.png" alt="Log Out">
                    <span>Attendance</span>
                </button>
                <button onclick="location.href='../../controllers/logout.php';" class="button-style">
                    <img src="../../assets/images/printer.png" alt="Log Out">
                    <span>Print</span>
                </button>
                <div id="content2">
                    <p>Outstanding :</p>
                    <p>Balance: 0.00</p>
                    <p>Total Balance: 0.00</p>
                    <div class="invoice-bill">
                        <!-- <h2>Next Bill No:</h2> -->
                    </div>

                </div>

                <div id="content2">
                    <p>Sub Total</p>
                    <p>Return Amount</p>
                    <p>Net Amount: 0.00</p>
                    <div class="invoice-bill">
                        <!-- <h2>Next Bill No:</h2> -->
                    </div>

                </div>
            </div>
        </div>
    </div>
</body>

</html>