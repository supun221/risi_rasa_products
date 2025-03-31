<?php
require_once '../models/Customer.php'; // Include the Customer class
require_once '../../config/databade.php';
class CustomerController
{
    private $customer;

    public function __construct($conn)
    {
        $this->customer = new Customer($conn);
    }

    // Add a new customer
    public function addCustomer($data)
    {
        try {
            if ($this->customer->customerExists($data['name'])) {
                return ['status' => 'error', 'message' => 'Customer with this name already exists.'];
            }

            $this->customer->addCustomer(
                $data['name'],
                $data['telephone'],
                $data['nic'],
                $data['address'],
                $data['whatsapp'],
                $data['email'],
                $data['birthday'],
                $data['credit_limit'],
                $data['discount'],
                $data['price_type']
            );

            return [
                'status' => 'success',
                'message' => 'Customer added successfully.',
                'redirect' => '../views/customers/customer_list.php' // Replace with your actual page
            ];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    // Search customers by query
    public function searchCustomers($query)
    {
        try {
            $results = $this->customer->searchCustomers($query);
            return ['status' => 'success', 'data' => $results];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    // In CustomerController class
    public function getCreditInfo($id)
    {
        try {
            $customer = $this->customer->getCustomerCreditInfo($id);
            if (!$customer) {
                return ['status' => 'error', 'message' => 'Customer not found.'];
            }
            return ['status' => 'success', 'data' => $customer];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Update customer details
    public function updateCustomer($id, $data)
    {
        try {
            $this->customer->updateCustomer(
                $id,
                $data['name'],
                $data['telephone'],
                $data['nic'],
                $data['address'],
                $data['whatsapp'],
                $data['email'],
                $data['birthday'],
                $data['credit_limit'],
                $data['discount'],
                $data['price_type']
            );

            return ['status' => 'success', 'message' => 'Customer updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Delete a customer
    public function deleteCustomer($id)
    {
        try {
            $this->customer->deleteCustomer($id);
            return ['status' => 'success', 'message' => 'Customer deleted successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Get customer details by ID
    public function getCustomer($id)
    {
        try {
            $customer = $this->customer->getCustomerById($id);
            if (!$customer) {
                return ['status' => 'error', 'message' => 'Customer not found.'];
            }
            return ['status' => 'success', 'data' => $customer];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Get customer details by name
    public function getCustomerByName($name)
    {
        try {
            $customer = $this->customer->getCustomerByName($name);
            if (!$customer) {
                return ['status' => 'error', 'message' => 'Customer not found.'];
            }
            return ['status' => 'success', 'data' => $customer];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    // Get customer details by phone number
    public function getCustomerByPhoneNumber($phone_number)
    {
        try {
            $customer = $this->customer->getCustomerByPhone($phone_number);
            if (!$customer) {
                return ['status' => 'error', 'message' => 'Customer not found.'];
            }
            return ['status' => 'success', 'data' => $customer];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

// Handle incoming HTTP POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once '../../config/databade.php'; // Ensure correct database path
    $controller = new CustomerController($conn);

    // Check if the request is JSON
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    // If JSON decoding failed, fallback to form data
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }
    $action = $data['action'] ?? null;
    $id = $data['id'] ?? null;
    $query = $data['query'] ?? null;
    // Prepare response
    $response = ['status' => 'error', 'message' => 'Invalid action.'];

    switch ($action) {
        case 'add':
            $response = $controller->addCustomer($data);
            break;

        case 'update':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required for update.'];
            } else {
                $response = $controller->updateCustomer($id, $data);
            }
            break;

        case 'delete':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required for deletion.'];
            } else {
                $response = $controller->deleteCustomer($id);
            }
            break;

        case 'get':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required for fetching data.'];
            } else {
                $response = $controller->getCustomer($id);
            }
            break;

        case 'getByName':
            $name = $data['name'] ?? null;
            if (!$name) {
                $response = ['status' => 'error', 'message' => 'Customer name is required for fetching data.'];
            } else {
                $response = $controller->getCustomerByName($name);
            }
            break;
        case 'searchCustomers':
            if (!$query) {
                throw new Exception('Search query is required.');
            }
            $response = $controller->searchCustomers($query);
            break;
        case 'searchCustomerByPhoneNumber':
            if (!$query) {
                throw new Exception('Search query is required.');
            }
            $response = $controller->getCustomerByPhoneNumber($query);
            break;
            // Add to the switch case in the POST handler
        case 'getCreditInfo':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required.'];
            } else {
                $response = $controller->getCreditInfo($id);
            }
            break;
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
