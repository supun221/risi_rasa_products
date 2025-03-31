<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customer Payments</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
    }

    .container {
      width: 100%;
      max-width: 600px;
      margin: 50px auto;
      background: #fff;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    label {
      display: block;
      margin-bottom: 5px;
    }

    select, input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
    }

    .btn {
      display: inline-block;
      background: #007bff;
      color: #fff;
      padding: 10px 15px;
      text-align: center;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      width: 100%;
      margin-top: 10px;
    }

    .btn:hover {
      background: #0056b3;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      width: 90%;
      max-width: 400px;
    }

    .modal-content h3 {
      margin-top: 0;
    }

    .close {
      float: right;
      font-size: 20px;
      cursor: pointer;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>Customer Payments</h2>
    <div class="form-group">
        <label for="customerName">Customer Name</label>
        <input type="text" id="customerName" placeholder="Enter customer name">
     
      //customer id
      <label for="paymentMode">Select Payment Mode</label>
      <select id="paymentMode">
        <option value="" selected disabled>Select Payment Mode</option>
        <option value="cash">Cash</option>
        <option value="online">Online Transfer</option>
        <option value="cheque">Cheque</option>
      </select>
    </div>
    <button class="btn" onclick="showModal()">Pay Now</button>
  </div>

  <!-- Modals -->
  <div class="modal" id="cashModal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('cashModal')">&times;</span>
      <h3>Cash Payment</h3>
      <div class="form-group">
        <label for="cashAmount">Enter Amount</label>
        <input type="number" id="cashAmount" placeholder="Enter amount">
      </div>
      <button class="btn" onclick="submitPayment()">Submit</button>
    </div>
  </div>

  <div class="modal" id="onlineModal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('onlineModal')">&times;</span>
      <h3>Online Transfer</h3>
      <div class="form-group">
        <label for="onlineDetails">Account Details</label>
        <input type="text" id="onlineDetails" placeholder="Enter account details">
      </div>
      <button class="btn" onclick="submitPayment()">Submit</button>
    </div>
  </div>

  <div class="modal" id="chequeModal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('chequeModal')">&times;</span>
      <h3>Cheque Payment</h3>
      <div class="form-group">
        <label for="bankName">Select Bank</label>
        <input type="text" id="bankName" placeholder="Enter bank name">
      </div>
      <div class="form-group">
        <label for="chequeNo">Cheque Number</label>
        <input type="text" id="chequeNo" placeholder="Enter cheque number">
      </div>
      <div class="form-group">
        <label for="chequeAmount">Amount</label>
        <input type="number" id="chequeAmount" placeholder="Enter amount">
      </div>
      <div class="form-group">
        <label for="chequeDate">Date</label>
        <input type="date" id="chequeDate">
      </div>
      <button class="btn" onclick="submitPayment()">Submit</button>
    </div>
  </div>

  <script>
    function showModal() {
      const paymentMode = document.getElementById('paymentMode').value;
      if (paymentMode === 'cash') {
        document.getElementById('cashModal').style.display = 'flex';
      } else if (paymentMode === 'online') {
        document.getElementById('onlineModal').style.display = 'flex';
      } else if (paymentMode === 'cheque') {
        document.getElementById('chequeModal').style.display = 'flex';
      } else {
        alert('Please select a payment mode.');
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).style.display = 'none';
    }

    function submitPayment() {
      alert('Payment submitted successfully!');
      closeModal('cashModal');
      closeModal('onlineModal');
      closeModal('chequeModal');
    }
  </script>
</body>
</html>
