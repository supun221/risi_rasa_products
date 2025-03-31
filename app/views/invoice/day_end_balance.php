<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../views/auth/login.php"); // Redirect to login if not logged in
    exit();
}

require_once '../../../config/databade.php'; // Include database configuration

// Ensure database connection is established
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Check if the user has already submitted a day-end balance today
$userId = $_SESSION['username'];
$today = date("Y-m-d");

$query = "SELECT * FROM day_end_balance WHERE username = ? AND date = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $userId, $today);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    echo '<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>';
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "Already submitted!",
                text: "You have already submitted a day-end balance today.",
                icon: "warning",
                confirmButtonText: "OK"
            }).then(() => {
                window.location.href = "day_end_view.php"; // Redirect to dashboard
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
    <title>Day End Balance</title>
    <link rel="stylesheet" href="openning_balance.css">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container">
        <!-- Left Sidebar -->
        <div class="left-sidebar">
            <button class="coin-btn" style="background-image: url('../../assets/images/1.jpg');" onclick="selectDenomination(1)" aria-label="Coin 1"></button>
            <button class="coin-btn" style="background-image: url('../../assets/images/2.jpg');" onclick="selectDenomination(2)" aria-label="Coin 2"></button>
            <button class="coin-btn" style="background-image: url('../../assets/images/rs5.jpg');" onclick="selectDenomination(5)" aria-label="Coin 5"></button>
            <button class="coin-btn" style="background-image: url('../../assets/images/10.jpg');" onclick="selectDenomination(10)" aria-label="Coin 10"></button>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h1 class="title">Day End Balance</h1>
            <div class="balance-display">
                <h2>Total Balance - <span class="total-balance" id="total-balance">0.00</span></h2>
            </div>

            <div class="balance-display2">
                <div class="denominations">
                    <?php
                    $denominations = [5000, 1000, 500, 100, 50, 20, 10, 5, 2, 1];
                    foreach ($denominations as $denom) {
                        echo "<div class='denomination-row' data-value='$denom'>
                                <span>$denom x </span>
                                <input type='number' min='0' value='' onchange='updateBalance(this)'>
                                <span>= <span class='calculated'>0</span></span>
                              </div>";
                    }
                    ?>
                </div>
            </div>
<!-- 
            <button class="submit-btn" id="submit-btn" onclick="saveBalanceToDB()">Submit Day End Balance</button> -->
        </div>

        <!-- Right Sidebar -->
        <div class="right-sidebar">
            <button class="note-btn" style="background-image: url('../../assets/images/20.jpg');" onclick="selectDenomination(20)" aria-label="20 Note"></button>
            <button class="note-btn" style="background-image: url('../../assets/images/50.jpg');" onclick="selectDenomination(50)" aria-label="50 Note"></button>
            <button class="note-btn" style="background-image: url('../../assets/images/100.jpg');" onclick="selectDenomination(100)" aria-label="100 Note"></button>
            <button class="note-btn" style="background-image: url('../../assets/images/500.jpg');" onclick="selectDenomination(500)" aria-label="500 Note"></button>
            <button class="note-btn" style="background-image: url('../../assets/images/1000.jpg');" onclick="selectDenomination(1000)" aria-label="1000 Note"></button>
            <button class="note-btn" style="background-image: url('../../assets/images/5000.jpg');" onclick="selectDenomination(5000)" aria-label="5000 Note"></button>
            <button class="go-invoice" id="go-invoice-btn" onclick="saveBalanceToDB()">Go To Invoice</button>
        </div>
    </div>
</body>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const inputs = document.querySelectorAll('.denomination-row input');
        const submitButton = document.getElementById('submit-btn');
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
                    submitButton.click();
                }
            });
        });

        submitButton.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                submitButton.click();
            }
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

        fetch('insert_day_end_balance.php', {
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
                    window.location.href = "day_end_view.php";
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