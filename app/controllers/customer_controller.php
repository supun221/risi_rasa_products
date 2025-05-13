<?php
require_once '../models/Customer.php'; // Include the Customer class
require_once '../../config/databade.php';

// Set headers for JSON responses and suppress warnings
header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ERROR);

// Start output buffering to prevent any unwanted output
ob_start();

// Create logs directory if it doesn't exist
$log_dir = '../../logs';
if (!is_dir($log_dir)) {
    mkdir($log_dir, 0777, true);
}

// Debug logging function
function debug_log($message, $data = null) {
    $log_file = '../../logs/customer_debug.log';
    $log_entry = date('Y-m-d H:i:s') . ' - ' . $message;
    if ($data !== null) {
        $log_entry .= ' - ' . json_encode($data);
    }
    try {
        file_put_contents($log_file, $log_entry . "\n", FILE_APPEND);
    } catch (Exception $e) {
        // Silently fail if logging isn't possible
    }
}

// Ensure clean output before sending JSON
function send_json_response($data) {
    // Clear any output buffered so far
    ob_clean();
    
    // Output JSON
    echo json_encode($data);
    
    // End the script
    exit;
}

// Check if there's JSON input
$request_body = file_get_contents('php://input');
$data = json_decode($request_body, true);

// Ensure the database has the route_id column
function ensureRouteIdColumn($conn) {
    // Check if route_id column exists in customers table
    $result = mysqli_query($conn, "SHOW COLUMNS FROM customers LIKE 'route_id'");
    
    if (!$result || mysqli_num_rows($result) === 0) {
        // If column doesn't exist, add it
        $alterQuery = "ALTER TABLE customers ADD COLUMN route_id INT(11) NULL DEFAULT NULL AFTER last_purchase_date";
        mysqli_query($conn, $alterQuery);
        
        // Add foreign key constraint if possible
        $checkRoutesTable = mysqli_query($conn, "SHOW TABLES LIKE 'routes'");
        if (mysqli_num_rows($checkRoutesTable) > 0) {
            $addFKQuery = "ALTER TABLE customers ADD CONSTRAINT fk_customer_route FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE SET NULL";
            mysqli_query($conn, $addFKQuery);
        }
    }
}

// Call this function at the start of the script
ensureRouteIdColumn($conn);

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
                $data['price_type'],
                $data['route_id'] // Include route_id
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
                $data['price_type'],
                $data['route_id'] // Include route_id
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
            // Sanitize inputs
            $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
            $telephone = mysqli_real_escape_string($conn, $_POST['telephone'] ?? '');
            $nic = mysqli_real_escape_string($conn, $_POST['nic'] ?? '');
            $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
            $whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp'] ?? '');
            $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
            $birthday = mysqli_real_escape_string($conn, $_POST['birthday'] ?? '');
            $credit_limit = mysqli_real_escape_string($conn, $_POST['credit_limit'] ?? '');
            $discount = mysqli_real_escape_string($conn, $_POST['discount'] ?? '');
            $price_type = mysqli_real_escape_string($conn, $_POST['price_type'] ?? '');
            $route_id = isset($_POST['route_id']) && $_POST['route_id'] !== '' ? $_POST['route_id'] : null;

            // Debug log
            debug_log("Adding customer with route_id", [
                'route_id_raw' => $_POST['route_id'] ?? 'not set',
                'route_id_processed' => $route_id
            ]);

            $sql = "INSERT INTO customers (
                name, telephone, nic, address, whatsapp, email, birthday, 
                credit_limit, discount, price_type, route_id
            ) VALUES (
                '$name', '$telephone', '$nic', '$address', '$whatsapp', 
                '$email', '$birthday', '$credit_limit', '$discount', '$price_type', " . 
                ($route_id === null ? "NULL" : "'$route_id'") . "
            )";

            debug_log("SQL Insert Query", $sql);

            if (mysqli_query($conn, $sql)) {
                send_json_response([
                    'status' => 'success',
                    'message' => 'Customer added successfully.'
                ]);
            } else {
                debug_log("Add customer - Error: " . mysqli_error($conn));
                send_json_response([
                    'status' => 'error',
                    'message' => 'Failed to add customer: ' . mysqli_error($conn)
                ]);
            }
            exit;

        case 'update':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required for update.'];
            } else {
                // Sanitize all inputs
                $name = mysqli_real_escape_string($conn, $_POST['name'] ?? '');
                $telephone = mysqli_real_escape_string($conn, $_POST['telephone'] ?? '');
                $nic = mysqli_real_escape_string($conn, $_POST['nic'] ?? '');
                $address = mysqli_real_escape_string($conn, $_POST['address'] ?? '');
                $whatsapp = mysqli_real_escape_string($conn, $_POST['whatsapp'] ?? '');
                $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
                $birthday = mysqli_real_escape_string($conn, $_POST['birthday'] ?? '');
                $credit_limit = mysqli_real_escape_string($conn, $_POST['credit_limit'] ?? '');
                $discount = mysqli_real_escape_string($conn, $_POST['discount'] ?? '');
                $price_type = mysqli_real_escape_string($conn, $_POST['price_type'] ?? '');
                $route_id = isset($_POST['route_id']) && $_POST['route_id'] !== '' ? $_POST['route_id'] : null;

                // Debug log
                debug_log("Updating customer with route_id", [
                    'id' => $id,
                    'route_id_raw' => $_POST['route_id'] ?? 'not set',
                    'route_id_processed' => $route_id
                ]);

                $sql = "UPDATE customers SET 
                    name = '$name', 
                    telephone = '$telephone', 
                    nic = '$nic', 
                    address = '$address', 
                    whatsapp = '$whatsapp', 
                    email = '$email', 
                    birthday = '$birthday', 
                    credit_limit = '$credit_limit', 
                    discount = '$discount', 
                    price_type = '$price_type',
                    route_id = " . ($route_id === null ? "NULL" : "'$route_id'") . "
                    WHERE id = '$id'";

                debug_log("SQL Update Query", $sql);

                if (mysqli_query($conn, $sql)) {
                    // After update, fetch the customer again to verify route_id was saved
                    $verify_sql = "SELECT route_id FROM customers WHERE id = '$id'";
                    $verify_result = mysqli_query($conn, $verify_sql);
                    $verify_row = mysqli_fetch_assoc($verify_result);
                    debug_log("Update customer - Verification - Saved route_id: " . var_export($verify_row['route_id'], true));
                    
                    send_json_response([
                        'status' => 'success',
                        'message' => 'Customer updated successfully.'
                    ]);
                } else {
                    debug_log("Update customer - Error: " . mysqli_error($conn));
                    send_json_response([
                        'status' => 'error',
                        'message' => 'Failed to update customer: ' . mysqli_error($conn)
                    ]);
                }
                exit;
            }
            break;

        case 'delete':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required for deletion.'];
            } else {
                $sql = "DELETE FROM customers WHERE id = '$id'";
                if (mysqli_query($conn, $sql)) {
                    send_json_response([
                        'status' => 'success',
                        'message' => 'Customer deleted successfully.'
                    ]);
                } else {
                    send_json_response([
                        'status' => 'error',
                        'message' => 'Failed to delete customer: ' . mysqli_error($conn)
                    ]);
                }
            }
            break;

        case 'get':
            if (!$id) {
                $response = ['status' => 'error', 'message' => 'Customer ID is required for fetching data.'];
            } else {
                $id = mysqli_real_escape_string($conn, $data['id'] ?? '');
                
                $sql = "SELECT id, name, telephone, nic, address, whatsapp, email, 
                       birthday, credit_limit, discount, price_type, route_id 
                       FROM customers 
                       WHERE id = '$id'";
                
                $result = mysqli_query($conn, $sql);
                
                if ($result && mysqli_num_rows($result) > 0) {
                    $customer = mysqli_fetch_assoc($result);
                    debug_log("Get customer - route_id: " . var_export($customer['route_id'], true));
                    
                    send_json_response([
                        'status' => 'success',
                        'data' => $customer
                    ]);
                } else {
                    send_json_response([
                        'status' => 'error',
                        'message' => 'Customer not found or error fetching data: ' . mysqli_error($conn)
                    ]);
                }
                exit; // Stop execution after sending JSON response
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
    send_json_response($response);
}
?>
