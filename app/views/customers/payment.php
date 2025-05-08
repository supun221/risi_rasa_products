<?php
// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Function to generate a unique invoice number
function generateInvoiceNumber() {
    $prefix = 'PAY';
    $date = date('Ymd');
    $random = strtoupper(substr(uniqid(), -4));
    return $prefix . $date . $random;
}

// Generate invoice number when page loads
$generatedInvoiceNumber = generateInvoiceNumber();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Customer Payment | Risi Rasa Products</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* General Layout & Typography */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }

        .page-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
            color: #2c3e50;
        }

        .content-wrapper {
            max-width: 900px;
            margin: 0 auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .back-button {
            background-color: #6c757d;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        /* Customer Info Card */
        .customer-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
        }

        .customer-info h2 {
            margin-top: 0;
            color: #2c3e50;
            font-weight: 600;
        }

        .customer-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .detail-item {
            margin-bottom: 8px;
        }

        .detail-label {
            font-weight: 600;
            color: #6c757d;
        }

        .credit-balance {
            font-size: 20px;
            font-weight: 700;
            color: #e74c3c;
            margin-top: 15px;
            padding: 10px;
            background-color: #fff;
            border-radius: 6px;
            text-align: center;
            border: 1px dashed #e74c3c;
        }

        .credit-limit {
            font-size: 20px;
            font-weight: 700;
            color: #e74c3c;
            margin-top: 15px;
            padding: 10px;
            background-color: #fff;
            border-radius: 6px;
            text-align: center;
            border: 1px dashed #e74c3c;
        }

        /* Payment Form */
        .payment-form {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            transition: border-color 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #4361ee;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .submit-btn {
            background-color: #4361ee;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .submit-btn:hover {
            background-color: #3249c2;
        }

        /* Payment History */
        .payment-history {
            margin-top: 30px;
        }

        .payment-history h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            font-size: 14px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            overflow: hidden;
        }

        table th {
            background-color: #f8f9fa;
            color: #516173;
            font-weight: 600;
            padding: 12px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #f0f0f0;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Print styles */
        @media print {
            .no-print {
                display: none !important;
            }
            .content-wrapper {
                box-shadow: none;
            }
        }
    </style>
</head>

<body>
    <?php
    require_once '../header1.php';
    require_once '../../../config/databade.php'; // Database connection

    // Check if customer ID is provided
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo "<script>window.location.href = 'customer_list.php';</script>";
        exit;
    }

    $customerId = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch customer information including credit_balance
    $customerQuery = "SELECT * FROM customers WHERE id = '$customerId'";
    $customerResult = mysqli_query($conn, $customerQuery);

    if (!$customerResult || mysqli_num_rows($customerResult) === 0) {
        echo "<script>alert('Customer not found!'); window.location.href = 'customer_list.php';</script>";
        exit;
    }

    $customer = mysqli_fetch_assoc($customerResult);
    
    // Get credit balance directly from the customer record
    $creditBalance = isset($customer['credit_balance']) ? $customer['credit_balance'] : 0.00;

    // Fetch payment history
    $paymentsQuery = "SELECT * FROM customer_payments WHERE customer_id = '$customerId' ORDER BY payment_date DESC";
    $paymentsResult = mysqli_query($conn, $paymentsQuery);
    $payments = [];

    if ($paymentsResult) {
        while ($row = mysqli_fetch_assoc($paymentsResult)) {
            $payments[] = $row;
        }
    }
    ?>

    <div class="content-wrapper">
        <!-- <div class="page-header">
            <h1>Customer Payment</h1>
            <a href="customer_list.php" class="back-button no-print">
                <i class="fas fa-arrow-left"></i> Back to Customers
            </a>
        </div> -->

        <?php if (isset($_SESSION['payment_error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i> <?php echo $_SESSION['payment_error']; unset($_SESSION['payment_error']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['payment_success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['payment_success']; unset($_SESSION['payment_success']); ?>
            </div>
        <?php endif; ?>

        <div class="customer-info">
            <h2><?php echo htmlspecialchars($customer['name']); ?></h2>
            <div class="customer-details">
                <div class="detail-item">
                    <span class="detail-label">Telephone:</span>
                    <?php echo htmlspecialchars($customer['telephone']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">NIC:</span>
                    <?php echo htmlspecialchars($customer['nic']); ?>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Address:</span>
                    <?php echo htmlspecialchars($customer['address']); ?>
                </div>
            </div>
            <div class="credit-balance">
                <span class="detail-label">Current Credit Balance:</span>
                Rs. <?php echo number_format((float)$creditBalance, 2); ?>
            </div>
            <div class="credit-limit">
                <span class="detail-label">Credit Limit:</span>
                Rs. <?php echo number_format((float)$customer['credit_limit'], 2); ?>
            </div>
        </div>

        <div class="payment-form no-print">
            <h3>Record Payment</h3>
            <form method="post" action="process_payment.php" id="paymentForm">
                <input type="hidden" name="customer_id" value="<?php echo htmlspecialchars($customerId); ?>">
                <input type="hidden" id="invoice_number" name="invoice_number" value="<?php echo htmlspecialchars($generatedInvoiceNumber); ?>">
                <input type="hidden" id="current_balance" value="<?php echo $creditBalance; ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label for="amount">Payment Amount (Rs.)</label>
                        <input type="number" id="amount" name="amount" class="form-control" step="0.01" required min="0.01" max="<?php echo $creditBalance; ?>">
                        <small class="form-text text-muted">Maximum payment amount: Rs. <?php echo number_format($creditBalance, 2); ?></small>
                    </div>
                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="card">Card</option>
                            <option value="cheque">Cheque</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="online">Online Payment</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group" id="cheque_number_container" style="display:none;">
                        <label for="cheque_number">Cheque Number</label>
                        <input type="text" id="cheque_number" name="cheque_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label for="reference">Reference (optional)</label>
                        <input type="text" id="reference" name="reference" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label for="notes">Notes (optional)</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Record Payment
                </button>
            </form>
        </div>

        <div class="payment-history">
            <h3>Payment History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Invoice</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($payments)): ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?php echo date('Y-m-d H:i', strtotime($payment['payment_date'])); ?></td>
                                <td>Rs. <?php echo number_format($payment['amount'], 2); ?></td>
                                <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                <td><?php echo $payment['invoice_number'] ? htmlspecialchars($payment['invoice_number']) : '-'; ?></td>
                                <td><?php echo $payment['reference'] ? htmlspecialchars($payment['reference']) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No payment history found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Display success/error messages with SweetAlert if available
            <?php if (isset($_SESSION['payment_success'])): ?>
                Swal.fire({
                    title: 'Success!',
                    text: '<?php echo $_SESSION['payment_success']; unset($_SESSION['payment_success']); ?>',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
            
            <?php if (isset($_SESSION['payment_error'])): ?>
                Swal.fire({
                    title: 'Error!',
                    text: '<?php echo $_SESSION['payment_error']; unset($_SESSION['payment_error']); ?>',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            <?php endif; ?>
            
            // Show/hide cheque number field based on payment method selection
            document.getElementById('payment_method').addEventListener('change', function() {
                const chequeNumberContainer = document.getElementById('cheque_number_container');
                if (this.value === 'cheque') {
                    chequeNumberContainer.style.display = 'block';
                    document.getElementById('cheque_number').setAttribute('required', 'required');
                } else {
                    chequeNumberContainer.style.display = 'none';
                    document.getElementById('cheque_number').removeAttribute('required');
                }
            });

            // Validate payment amount
            document.getElementById('paymentForm').addEventListener('submit', function(e) {
                const amount = parseFloat(document.getElementById('amount').value);
                const currentBalance = parseFloat(document.getElementById('current_balance').value);
                
                if (amount > currentBalance) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Invalid Amount!',
                        text: 'Payment amount cannot exceed current credit balance.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            });
        });
    </script>
</body>
</html>
