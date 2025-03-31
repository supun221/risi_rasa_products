<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../views/auth/login.php"); // Redirect to login if not logged in
    exit();
}

require_once '../../../config/databade.php'; // Include your database configuration

// Ensure database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if the user has already submitted an opening balance today
$userId = $_SESSION['username'];
$today = date("Y-m-d"); // Get today's date

$query = "SELECT * FROM opening_balance WHERE username = ? AND date = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $userId, $today); // Fix: "ss" for string parameters
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Already Submitted!",
                text: "You have already submitted an opening balance today.",
                icon: "warning",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "create_invoice2.php";
            });
        });
    </script>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Opening Balance</title>
    <link rel="stylesheet" href="openning_balance.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <div class="main-content">
            <h1 class="title">Opening Balance</h1>
            <div class="balance-display">
                <h2>Total Balance - <span class="total-balance" id="total-balance">0.00</span></h2>
            </div>
            
            <!-- Balance Display -->
            <div class="balance-display2">
                <div class="denominations">
                    <?php
                    $denominations = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];
                    foreach ($denominations as $denom) {
                        echo "<div class='denomination-row' data-value='$denom'>
                                <span>Rs $denom x </span>
                                <input type='number' min='0' value='' onchange='updateBalance(this)'>
                                <span>= Rs <span class='calculated'>0</span></span>
                              </div>";
                    }
                    ?>
                </div>
            </div>
            
            <button class="go-invoice" id="go-invoice-btn" onclick="saveBalanceToDB()">
                <i class="fas fa-arrow-right"></i> Continue to Invoice
            </button>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputs = document.querySelectorAll('.denomination-row input');
            const invoiceButton = document.getElementById('go-invoice-btn');

            if (inputs.length > 0) inputs[0].focus();

            inputs.forEach((input, index) => {
                input.addEventListener('keydown', (event) => {
                    if (event.key === 'ArrowDown' && index < inputs.length - 1) {
                        inputs[index + 1].focus();
                        event.preventDefault();
                    } else if (event.key === 'ArrowUp' && index > 0) {
                        inputs[index - 1].focus();
                        event.preventDefault();
                    } else if (event.key === 'Enter' && index === inputs.length - 1) {
                        invoiceButton.click();
                    }
                });
            });

            invoiceButton.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    invoiceButton.click();
                }
            });
        });

        function saveBalanceToDB() {
            const totalBalance = document.getElementById('total-balance').textContent;
            const denominations = {};

            document.querySelectorAll('.denomination-row').forEach(row => {
                const value = row.dataset.value;
                const quantity = row.querySelector('input').value || 0;
                denominations[`denomination_${value}`] = parseInt(quantity);
            });

            const data = {
                total_balance: parseFloat(totalBalance),
                ...denominations
            };

            fetch('insert_opening_balance.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                Swal.fire({
                    title: result.success ? "Success" : "Error!",
                    text: result.message,
                    icon: result.success ? 'success' : 'error',
                    confirmButtonText: 'OK'
                }).then((swalResult) => {
                    if (swalResult.isConfirmed && result.success) {
                        window.location.href = "create_invoice2.php";
                    }
                });
            })
            .catch(error => console.error('Error:', error));
        }

        function updateBalance(inputElement) {
            const denominationRow = inputElement.closest('.denomination-row');
            const denominationValue = parseInt(denominationRow.dataset.value);
            const quantity = parseInt(inputElement.value) || 0;
            const calculatedAmount = denominationValue * quantity;

            denominationRow.querySelector('.calculated').textContent = calculatedAmount;
            updateTotalBalance();
        }

        function updateTotalBalance() {
            let total = 0;
            document.querySelectorAll('.denomination-row').forEach(row => {
                total += parseInt(row.querySelector('.calculated').textContent) || 0;
            });
            document.getElementById('total-balance').textContent = total;
        }

        function selectDenomination(value) {
            const targetRow = document.querySelector(`.denomination-row[data-value="${value}"]`);
            if (targetRow) {
                targetRow.querySelector('input').focus();
            }
        }
    </script>

</html>
