<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Supplier</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        /* Your existing styles */
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Update Supplier</h2>

        <?php
        // Include necessary files
        require_once '../../../config/databade.php';
        require_once '../../controllers/supplier_controller.php';

        // Fetch supplier data by ID
        $supplierId = isset($_GET['id']) ? intval($_GET['id']) : null;
        $supplierData = [];

        if ($supplierId) {
            $controller = new SupplierController($conn);
            $response = $controller->getSupplier($supplierId);

            if ($response && $response['status'] === 'success') {
                $supplierData = $response['data'];
            } else {
                echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($response['message'] ?? 'Supplier not found.') . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Supplier ID is required to update details.</div>';
            exit;
        }
        ?>

        <!-- Form to update supplier details -->
        <form action="../../controllers/supplier_controller.php" method="POST">
            <!-- Hidden inputs -->
            <input type="hidden" name="supplier_id" value="<?php echo htmlspecialchars($supplierId); ?>">
            <input type="hidden" name="action" value="update">

            <!-- Input fields for supplier details -->
            <div class="form-group">
                <label for="supplier_name">Supplier Name</label>
                <input type="text" class="form-control" id="supplier_name" name="supplier_name"
                    value="<?php echo htmlspecialchars($supplierData['supplier_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="telephone">Telephone</label>
                <input type="number" class="form-control" id="telephone" name="telephone"
                    value="<?php echo htmlspecialchars($supplierData['telephone_no'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="company">Company</label>
                <input type="text" class="form-control" id="company" name="company"
                    value="<?php echo htmlspecialchars($supplierData['company'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="area_manager">Area Manager</label>
                <input type="text" class="form-control" id="area_manager" name="area_manager"
                    value="<?php echo htmlspecialchars($supplierData['Area_manager'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="agent_details">Agent Details</label>
                <textarea class="form-control" id="agent_details" name="agent_details"
                    rows="3"><?php echo htmlspecialchars($supplierData['agent_details'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="ref_details">Reference Details</label>
                <textarea class="form-control" id="ref_details" name="ref_details"
                    rows="3"><?php echo htmlspecialchars($supplierData['ref_details'] ?? ''); ?></textarea>
            </div>

            <!-- Submit button -->
            <button type="submit" class="btn btn-primary">Update Supplier</button>
            
        </form>
    </div>
    <script>
       
        </script>

</body>

</html>
