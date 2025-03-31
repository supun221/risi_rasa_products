<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supplier Management</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php require_once '../header1.php'; ?>
<div class="container mt-5">
    <!-- Button to open the Add Supplier modal -->
    <button class="btn btn-primary mb-4" id="addSupplierBtn">+ Add Supplier</button>

    <!-- Table Section -->
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Id</th>
                <th>Supplier Name</th>
                <th>Telephone No</th>
                <th>Company</th>
                <th>Credit Balance</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="supplierTable">
            <!-- Table rows are dynamically inserted here -->
        </tbody>
    </table>

    <!-- Pagination Buttons -->
    <div class="d-flex justify-content-between">
        <button class="btn btn-secondary" id="prevPageBtn" disabled>Previous</button>
        <button class="btn btn-secondary" id="nextPageBtn" disabled>Next</button>
    </div>
</div>

<!-- Modal for Adding/Editing Supplier -->
<div class="modal fade" id="supplierModal" tabindex="-1" aria-labelledby="supplierModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supplierModalLabel">Register Supplier</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                    <button type="submit" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let currentPage = 1;
    const recordsPerPage = 5;
    const prevPageBtn = document.getElementById('prevPageBtn');
    const nextPageBtn = document.getElementById('nextPageBtn');

    const supplierModal = new bootstrap.Modal(document.getElementById('supplierModal'));

    // Open modal for adding a new supplier
    document.getElementById('addSupplierBtn').addEventListener('click', () => {
        document.getElementById('supplierForm').reset();
        document.getElementById('supplierId').value = '';
        supplierModal.show();
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
            supplierModal.hide();
            loadSuppliers(currentPage);
        } else {
            Swal.fire('Error', result.message, 'error');
        }
    });

    // Load suppliers into the table with pagination
    async function loadSuppliers(page) {
        const response = await fetch(`get_suppliers.php?page=${page}&limit=${recordsPerPage}`);
        const { suppliers, totalPages } = await response.json();

        const supplierTable = document.getElementById('supplierTable');
        supplierTable.innerHTML = '';

        suppliers.forEach((supplier) => {
            supplierTable.innerHTML += `
                <tr>
                    <td>${supplier.id}</td>
                    <td>${supplier.supplier_name}</td>
                    <td>${supplier.telephone_no}</td>
                    <td>${supplier.company}</td>
                    <td>${supplier.credit_balance}</td>
                    <td>
                        <button class="btn btn-sm btn-warning" onclick="editSupplier(${supplier.id})">Edit</button>
                    </td>
                </tr>
            `;
        });

        // Enable or disable pagination buttons
        prevPageBtn.disabled = page === 1;
        nextPageBtn.disabled = page === totalPages;
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

        supplierModal.show();
    }

    // Pagination button event listeners
    prevPageBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadSuppliers(currentPage);
        }
    });

    nextPageBtn.addEventListener('click', () => {
        currentPage++;
        loadSuppliers(currentPage);
    });

    // Load initial data
    loadSuppliers(currentPage);
    document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });

</script>
</body>
</html>
