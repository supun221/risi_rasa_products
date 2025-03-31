<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Day End Shift Report</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/js/all.min.js"
        crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./create-invoice.styles.css">
    <link rel="stylesheet" href="../../assets/css/day_end.css">
</head>
<style>
    /* Highlight row styling */
    .highlight-row {
        background-color: #ffffe0;
        /* Light yellow background */
        border: 2px solid #ffd700;
        /* Gold border for emphasis */
        border-radius: 5px;
        /* Rounded corners (optional) */
        padding: 10px;
        /* Adds spacing inside the row */
        margin: 5px 0;
        /* Adds space between rows */
        width: 720px;
    }

    /* Make text bold */
    .bold-text {
        font-weight: bold;
    }
</style>

<body>
    <!-- <div class="header-container-rmaster">
        <div class="text-partition-cont">
       
            <div class="content-row">
                <div class="timer-container">
                    <span id="current-date">November 21, 2024</span>
                    <span id="current-time">7:28:50 AM</span>
                </div>
                <div class="company-info">
                    <span class="heading-sinhala">එග්ලන්ඩ් සුපර්</span>
                    <span class="heading-english">Eggland Super</span>
                    <span class="company-motto">හොඳම දේ අඩුම මිලට</span>
                </div>
            </div>
        </div>
    </div> -->
    <div class="day-end-form">
        <div class="form-title">Day End Shift Report</div>
        <div class="data-row"><span><i class="fas fa-user"></i> Username:</span> <span id="username">-</span></div>

        <div class="data-row"><span><i class="fas fa-wallet"></i> Opening Balance:</span> <span id="opening_balance">-</span></div>

        <div class="data-row"><span><i class="fas fa-money-bill-wave"></i> Total Gross Amount:</span> <span
                id="total_gross">-</span></div>
        <div class="data-row"><span><i class="fas fa-chart-bar"></i> Total Net Amount:</span> <span
                id="total_net">-</span></div>
        <div class="data-row"><span><i class="fas fa-gift"></i> Total Discount:</span> <span
                id="total_discount">-</span></div>
        <div class="data-row"><span><i class="fas fa-file-invoice"></i> Total Bills:</span> <span
                id="total_bills">-</span></div>
        <div class="data-row"><span><i class="fas fa-cash-register"></i> Total Cash Payments:</span> <span
                id="total_cash">-</span></div>
        <div class="data-row"><span><i class="fas fa-credit-card"></i> Total Card Payments:</span> <span
                id="total_credit">-</span></div>
        <div class="data-row"><span><i class="fas fa-file-invoice"></i> Total Bill Payments:</span> <span
                id="bill_payment">-</span></div>
        <div class="data-row"><span><i class="fas fa-cash-register"></i> Today Cash Drawer Payment:</span> <span
                id="cash_drawer">-</span></div>
        <div class="data-row"><span><i class="fas fa-gift"></i> Total Voucher Payments:</span> <span
                id="voucher_payment">-</span></div>
        <div class="data-row"><span><i class="fas fa-donate"></i> Total Free Payments:</span> <span
                id="free_payment">-</span></div>
        <div class="data-row"><span><i class="fas fa-balance-scale"></i> Total Balance:</span> <span
                id="total_balance">-</span></div>
        <div class="data-row"><span><i class="fas fa-balance-scale"></i> Day End Hand Balance:</span> <span id="day_end_hand_balance">-</span></div>
        <div class="data-row">

            <span><i class="fas fa-hand-holding-usd"></i> Total Full Balance:</span>
            <span id="cash_balance" class="bold-text">-</span>
        </div>
        <div class="data-row"><span><i class="fas fa-exchange-alt"></i> To Day Balance:</span> <span
                id="difference">-</span></div>
        <div class="data-row"></div>

        <div class="data-row"><span><i class="fas fa-exchange-alt"></i> Difference</span> <span
                id="differencehand">-</span></div>
        <div class="data-row">




            <div class="btn-container">
                <button class="action-btn" id="backBtn">
                    <i class="fas fa-arrow-left"></i> Back
                </button>
                <button class="action-btn" id="printBtn">
                    <i class="fas fa-print"></i> Print
                </button>
                <button class="action-btn" id="saveBtn">
                    <i class="fas fa-save"></i> Save Report
                </button>
            </div>

        </div>
        <script>
            document.getElementById('saveBtn').addEventListener('click', function() {
                const reportData = {
                    username: document.getElementById('username').textContent.trim(),
                    opening_balance: parseFloat(document.getElementById('opening_balance').textContent) || 0.00,
                    total_gross: parseFloat(document.getElementById('total_gross').textContent) || 0.00,
                    total_net: parseFloat(document.getElementById('total_net').textContent) || 0.00,
                    total_discount: parseFloat(document.getElementById('total_discount').textContent) || 0.00,
                    total_bills: parseInt(document.getElementById('total_bills').textContent) || 0,
                    total_cash: parseFloat(document.getElementById('total_cash').textContent) || 0.00,
                    total_credit: parseFloat(document.getElementById('total_credit').textContent) || 0.00,
                    bill_payment: parseFloat(document.getElementById('bill_payment').textContent) || 0.00,
                    cash_drawer: parseFloat(document.getElementById('cash_drawer').textContent) || 0.00,
                    voucher_payment: parseFloat(document.getElementById('voucher_payment').textContent) || 0.00,
                    free_payment: parseFloat(document.getElementById('free_payment').textContent) || 0.00,
                    total_balance: parseFloat(document.getElementById('total_balance').textContent) || 0.00,
                    day_end_hand_balance: parseFloat(document.getElementById('day_end_hand_balance').textContent) || 0.00,
                    cash_balance: parseFloat(document.getElementById('cash_balance').textContent) || 0.00,
                    today_balance: parseFloat(document.getElementById('difference').textContent) || 0.00,
                    difference_hand: parseFloat(document.getElementById('differencehand').textContent) || 0.00
                };

                fetch('../../controllers/day_end_all_controler.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            saveDayEndReport: true,
                            ...reportData
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Report saved successfully!");
                        } else {
                            alert("Error saving report: " + data.error);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        </script>
        <script>
            // Fetch data when the page loads
            document.addEventListener('DOMContentLoaded', function() {
                fetchOpeningBalance();
                fetchUsername();
                fetchDayEndData();
                fetchTodayCashDrawerPayment();
                fetchDayEndHandBalance();
            });

            function fetchUsername() {
                fetch('../../controllers/day_end_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'getUsername=true'
                    })
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('username').textContent = data.username || 'N/A';
                        window.username = data.username; // Store username globally
                    })
                    .catch(error => console.error('Error:', error));
            }

            function fetchOpeningBalance() {
                fetch('../../controllers/day_end_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'getOpeningBalance=true',
                    })
                    .then(response => response.json())
                    .then(data => {
                        // Parse the total_balance sent by the backend
                        const openingBalance = parseFloat(data.total_balance || '0.00');
                        document.getElementById('opening_balance').textContent = openingBalance.toFixed(2);

                        // Store the value for further calculations
                        window.openingBalance = openingBalance;
                        calculateCashBalanceAndDifference(); // Trigger calculation after fetching data
                    })
                    .catch(error => console.error('Error:', error));
            }

            function fetchDayEndData() {
                fetch('../../controllers/day_end_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'getDayEndData=true',
                    })
                    .then(response => response.json())
                    .then(data => {
                        const totalNet = parseFloat(data.total_net || '0.00');
                        document.getElementById('total_gross').textContent = data.total_gross || '0.00';
                        document.getElementById('total_net').textContent = totalNet.toFixed(2);
                        document.getElementById('total_discount').textContent = data.total_discount || '0.00';
                        document.getElementById('total_bills').textContent = data.total_bills || '0';
                        document.getElementById('total_cash').textContent = data.total_cash || '0.00';
                        document.getElementById('total_credit').textContent = data.total_credit || '0.00';
                        document.getElementById('bill_payment').textContent = data.total_bill_payment || '0.00';
                        document.getElementById('voucher_payment').textContent = data.total_voucher_payment || '0.00';
                        document.getElementById('free_payment').textContent = data.total_free_payment || '0.00';
                        document.getElementById('total_balance').textContent = data.total_balance || '0.00';

                        // Store the total_net value for further calculations
                        window.totalNet = totalNet;
                        calculateCashBalanceAndDifference(); // Trigger calculation after fetching data
                    })
                    .catch(error => console.error('Error:', error));
            }

            function fetchDayEndHandBalance() {
                fetch('../../controllers/day_end_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'getDayEndHandBalance=true',
                    })
                    .then(response => response.json())
                    .then(data => {
                        const dayEndHandBalance = parseFloat(data.total_balance || '0.00');
                        document.getElementById('day_end_hand_balance').textContent = dayEndHandBalance.toFixed(2);

                        // Store the value for further calculations
                        window.dayEndHandBalance = dayEndHandBalance;
                        calculateCashBalanceAndDifference(); // Trigger calculation after fetching data
                    })
                    .catch(error => console.error('Error:', error));
            }

            function fetchTodayCashDrawerPayment() {
                fetch('../../controllers/day_end_controller.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'getTodayCashDrawerPayment=true',
                    })
                    .then(response => response.json())
                    .then(data => {
                        const cashDrawer = parseFloat(data.total_cash_drawer || '0.00');
                        document.getElementById('cash_drawer').textContent = cashDrawer.toFixed(2);

                        // Store the cash drawer value for further calculations
                        window.cashDrawer = cashDrawer;
                        calculateCashBalanceAndDifference(); // Trigger calculation after fetching data
                    })
                    .catch(error => console.error('Error:', error));
            }

            function calculateCashBalanceAndDifference() {
                // Ensure all required data is fetched before performing calculations
                if (
                    window.openingBalance !== undefined &&
                    window.totalNet !== undefined &&
                    window.cashDrawer !== undefined &&
                    window.dayEndHandBalance !== undefined
                ) {
                    // Calculate Cash Balance
                    const cashBalance = window.openingBalance + window.totalNet;
                    document.getElementById('cash_balance').textContent = cashBalance.toFixed(2);

                    // Calculate Difference (Cash Balance - Cash Drawer)
                    const difference = cashBalance - window.cashDrawer;
                    document.getElementById('difference').textContent = difference.toFixed(2);

                    // Calculate Difference Hand (Day End Hand Balance - Cash Balance)
                    const differenceHand = window.dayEndHandBalance - difference;
                    document.getElementById('differencehand').textContent = differenceHand.toFixed(2);
                }
            }

            document.getElementById('backBtn').addEventListener('click', function() {
                window.history.back();
            });

            document.getElementById('printBtn').addEventListener('click', function() {
                window.print();
            });

            document.addEventListener('DOMContentLoaded', () => {
                updateDateTime();
                setInterval(updateDateTime, 1000);
            });

            function updateDateTime() {
                const now = new Date();
                const timeElement = document.getElementById('current-time');
                timeElement.textContent = now.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });

                const dateElement = document.getElementById('current-date');
                dateElement.textContent = now.toLocaleDateString('en-US', {
                    month: 'long',
                    day: 'numeric',
                    year: 'numeric'
                });
            }
        </script>


</body>

</html>