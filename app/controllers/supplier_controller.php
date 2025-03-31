<?php
require_once 'Supplier.php'; // Include the Supplier class
require_once '../../config/databade.php'; // Corrected spelling of 'database.php'

class SupplierController
{
    private $supplier;

    public function __construct($conn)
    {
        $this->supplier = new Supplier($conn);
    }

    // Add a new supplier
    public function addSupplier($data)
    {
        try {
            // Set default value for credit_balance if not provided
            $creditBalance = $data['credit_balance'] ?? 0;

            $this->supplier->addSupplier(
                $data['supplier_name'],
                $data['telephone'],
                $data['company'],
                $creditBalance,
                $data['area_manager'],
                $data['agent_details'],
                $data['ref_details']
            );

            return [
                'status' => 'success',
                'message' => 'Supplier added successfully.',
                'redirect' => '../views/suppliers/supplier_list.php'
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Search suppliers
    public function searchSupplier($query)
    {
        try {
            $result = $this->supplier->searchSupplier($query);

            // Return results or empty array message
            return [
                'status' => 'success',
                'data' => $result,
                'message' => empty($result) ? 'No suppliers found matching your query.' : 'Suppliers found.'
            ];
        } catch (Exception $e) {
            // Log error for debugging
            error_log("Search error: " . $e->getMessage());
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }



    // Update supplier details
    public function updateSupplier($supplier_id, $data)
    {
        try {
            $creditBalance = $data['credit_balance'] ?? 0;

            $this->supplier->updateSupplier(
                $supplier_id,
                $data['supplier_name'],
                $data['telephone'],
                $data['company'],
                $creditBalance,
                $data['area_manager'],
                $data['agent_details'],
                $data['ref_details']
            );

            return [
                'status' => 'success',
                'message' => 'Supplier updated successfully.',
                'redirect' => '../views/suppliers/supplier_list.php'
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Delete a supplier
    public function deleteSupplier($id)
    {
        try {
            $this->supplier->deleteSupplier($id);
            return ['status' => 'success', 'message' => 'Supplier deleted successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Get supplier details by ID
    public function getSupplier($supplier_id)
    {
        try {
            $supplier = $this->supplier->getSupplierById($supplier_id);
            if (!$supplier) {
                return ['status' => 'error', 'message' => 'Supplier not found.'];
            }
            return ['status' => 'success', 'data' => $supplier];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

// Example usage
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../config/databade.php'; // Correct spelling
    $controller = new SupplierController($conn);

    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // Fallback to form data if JSON decoding fails
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }

    $action = $data['action'] ?? null;
    $supplierId = $data['supplier_id'] ?? null;
    $query = $data['query'] ?? null; // Ensure the search query is captured
    $response = ['status' => 'error', 'message' => 'Invalid action.'];

    switch ($action) {
        case 'add':
            $response = $controller->addSupplier($data);
            break;

        case 'update':
            if (!$supplierId) {
                $response = ['status' => 'error', 'message' => 'Supplier ID is required for update.'];
            } else {
                $response = $controller->updateSupplier($supplierId, $data);
            }
            break;

        case 'delete':
            if (!$supplierId) {
                $response = ['status' => 'error', 'message' => 'Supplier ID is required for deletion.'];
            } else {
                $response = $controller->deleteSupplier($supplierId);
            }
            break;

        case 'get':
            if (!$supplierId) {
                $response = ['status' => 'error', 'message' => 'Supplier ID is required for fetching data.'];
            } else {
                $response = $controller->getSupplier($supplierId);
            }
            break;

        case 'search':
            if (!$query) {
                $response = ['status' => 'error', 'message' => 'Search query is required.'];
            } else {
                $response = $controller->searchSupplier($query);
            }
            break;

        default:
            $response = ['status' => 'error', 'message' => 'Invalid action.'];
    }

    header('Content-Type: application/json');
    echo json_encode($response);

    // Logging
    error_log("Request Received - Action: $action, Supplier ID: $supplierId, Query: $query");
    error_log("Request Data: " . print_r($data, true));
}
