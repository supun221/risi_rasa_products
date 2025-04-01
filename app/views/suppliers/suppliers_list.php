<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management | Risi Rasa Products</title>
    <link rel="stylesheet" href="../../assets/css/user_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* General Layout & Typography */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
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
            max-width: 1400px;
            margin: 0 auto;
            padding: 25px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        /* Action Button */
        .action-btn {
            background-color: #4361ee;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 12px 20px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        .action-btn:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 5px 8px rgba(67, 97, 238, 0.3);
        }

        .action-btn:active {
            transform: translateY(0);
        }

        .action-btn i {
            font-size: 16px;
        }

        /* Table Styles */
        .table-container {
            overflow-y: auto;
            max-height: 70vh;
            margin-top: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background-color: #fff;
            font-size: 14px;
        }

        table th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            color: #516173;
            font-weight: 600;
            padding: 14px 12px;
            text-align: left;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            z-index: 10;
        }

        table td {
            padding: 14px 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #3a3f51;
            vertical-align: middle;
        }

        table tr:last-child td {
            border-bottom: none;
        }

        table tr:hover {
            background-color: #f8faff;
        }

        /* Credit Balance Styling */
        .credit-balance {
            font-weight: 600;
            color: #2c3e50;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-buttons button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px;
            border-radius: 6px;
            color: white;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 36px;
            height: 36px;
        }

        .action-btn-edit {
            background-color: #4361ee;
        }

        .action-btn-edit:hover {
            background-color: #3249c2;
            transform: translateY(-2px);
            box-shadow: 0 3px 6px rgba(67, 97, 238, 0.2);
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-dialog {
            position: relative;
            width: 500px;
            margin: 100px auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            padding: 0;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #e0e0e0;
            background-color: #f8f9fa;
            border-radius: 8px 8px 0 0;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }

        .btn-close {
            background: none;
            border: none;
            font-size: 20px;
            color: #516173;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #516173;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 15px;
        }

        .form-control:focus {
            outline: none;
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
    </style>
</head>
<body>
<?php require_once '../header1.php'; ?>

<div class="content-wrapper">
    <div class="page-header">
        <h1>Supplier Management</h1>
        <button class="action-btn" id="addSupplierBtn">
            <i class="fas fa-plus-circle"></i> Add Supplier
        </button>
    </div>

    <!-- Table Section -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th width="10%">ID</th>
                    <th width="25%">Supplier Name</th>
                    <th width="20%">Telephone</th>
                    <th width="25%">Company</th>
                    <th width="10%">Credit Balance</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody id="supplierTable">
                <!-- Table rows are dynamically inserted here -->
            </tbody>
        </table>
    </div>
</div>

<!-- Modal for Adding/Editing Supplier -->
<div class="modal" id="supplierModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierModalLabel">Register Supplier</h5>
                <button type="button" class="btn-close" id="closeModalBtn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="supplierForm">
                    <input type="hidden" id="supplierId" name="supplierId">
                    <div class="mb-3">
                        <label for="supplierName" class="form-label">Supplier Name:</label>
                        <input type="text" id="supplierName" name="supplierName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="telephoneNo" class="form-label">Telephone No#:</label>
                        <input type="text" id="telephoneNo" name="telephoneNo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="company" class="form-label">Company:</label>
                        <input type="text" id="company" name="company" class="form-control" required>
                    </div>
                    <button type="submit" class="action-btn">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Modal handling
    const modal = document.getElementById('supplierModal');
    const addSupplierBtn = document.getElementById('addSupplierBtn');
    const closeModalBtn = document.getElementById('closeModalBtn');
    
    addSupplierBtn.addEventListener('click', () => {
        document.getElementById('supplierForm').reset();
        document.getElementById('supplierId').value = '';
        document.getElementById('supplierModalLabel').textContent = 'Register Supplier';
        modal.style.display = 'block';
    });
    
    closeModalBtn.addEventListener('click', () => {
        modal.style.display = 'none';
    });
    
    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Handle form submission for adding/updating supplier
    document.getElementById('supplierForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);

        const response = await fetch('supplier_controller.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.json();

        if (result.status === 'success') {
            Swal.fire('Success', result.message, 'success');
            modal.style.display = 'none';
            loadSuppliers();
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    });

    // Load all suppliers into the table
    async function loadSuppliers() {
        const response = await fetch('get_suppliers.php');
        const data = await response.json();
        const suppliers = data.suppliers;

        const supplierTable = document.getElementById('supplierTable');
        supplierTable.innerHTML = '';

        if (suppliers.length === 0) {
            supplierTable.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;">No suppliers found.</td></tr>';
            return;
        }

        suppliers.forEach((supplier) => {
            supplierTable.innerHTML += `
                <tr>
                    <td>${supplier.id}</td>
                    <td><strong>${supplier.supplier_name}</strong></td>
                    <td>${supplier.telephone_no}</td>
                    <td>${supplier.company}</td>
                    <td class="credit-balance">${formatCurrency(supplier.credit_balance)}</td>
                    <td class="action-buttons">
                        <button class="action-btn-edit" onclick="editSupplier(${supplier.id})" title="Edit Supplier">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                    </td>
                </tr>
            `;
        });
    }

    // Format currency values
    function formatCurrency(value) {
        const numValue = parseFloat(value || 0);
        return numValue.toFixed(2);
    }

    // Populate the form for editing a supplier
    async function editSupplier(id) {
        const response = await fetch(`get_suppliers.php?id=${id}`);
        const supplier = await response.json();

        if (!supplier || supplier.status === 'error') {
            Swal.fire('Error', supplier.message || 'Unable to fetch supplier data.', 'error');
            return;
        }

        document.getElementById('supplierId').value = supplier.id;
        document.getElementById('supplierName').value = supplier.supplier_name;
        document.getElementById('telephoneNo').value = supplier.telephone_no;
        document.getElementById('company').value = supplier.company;
        document.getElementById('supplierModalLabel').textContent = 'Edit Supplier';

        modal.style.display = 'block';
    }

    // Load initial data
    loadSuppliers();
    
    document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });
</script>
</body>
</html>
