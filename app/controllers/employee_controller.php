<?php
require_once '../models/Employee.php';
require_once '../../config/databade.php';

header('Content-Type: application/json');

class EmployeeController {
    private $employee;

    public function __construct($conn) {
        $this->employee = new Employee($conn);
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $input = file_get_contents("php://input");
        $data = json_decode($input, true) ?: $_POST;

        $action = $data['action'] ?? null;
        $emp_id = $data['emp_id'] ?? null;

        if ($method === 'POST' && $action) {
            switch ($action) {
                case 'add':
                    $this->handleAddEmployee($data);
                    break;
                case 'update':
                    $this->handleUpdateEmployee($data);
                    break;
                case 'delete':
                    $this->handleDeleteEmployee($emp_id);
                    break;
                case 'get':
                    $this->handleGetEmployee($emp_id);
                    break;
                default:
                    $this->sendResponse(["error" => "Invalid action"]);
            }
        } else {
            $this->sendResponse(["error" => "Invalid HTTP request"]);
        }
    }

    private function handleAddEmployee($data) {
        $name = $data['name'] ?? null;
        $nic = $data['nic'] ?? null;
        $telephone = $data['telephone'] ?? null;
        $address = $data['address'] ?? null;

        if ($name && $nic && $telephone && $address) {
            try {
                $empId = $empId = $this->employee->addEmployee($name, $nic, $telephone, $address);
                $this->sendResponse([
                    
                    "success" => true,
                   
                    "message" => "Employee added successfully!",
                    "emp_id" => $empId // Return emp_id for barcode generation
                ,
                    "emp_id" => $empId
                ]);
            } catch (Exception $e) {
                $this->sendResponse(["error" => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(["error" => "Missing required fields"]);
        }
    }


    private function handleUpdateEmployee($data) {
        $emp_id = $data['emp_id'] ?? null;
        $name = $data['name'] ?? null;
        $nic = $data['nic'] ?? null;
        $telephone = $data['telephone'] ?? null;
        $address = $data['address'] ?? null;

        if ($emp_id && $name && $nic && $telephone && $address) {
            try {
                $this->employee->updateEmployee($emp_id, $name, $nic, $telephone, $address);
                $this->sendResponse(["success" => true, "message" => "Employee updated successfully!"]);
            } catch (Exception $e) {
                $this->sendResponse(["error" => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(["error" => "Missing required fields"]);
        }
    }

    private function handleDeleteEmployee($emp_id) {
        if ($emp_id) {
            try {
                $result = $this->employee->deleteEmployee($emp_id);
                echo json_encode(["status" => "success", "message" => "Employee deleted successfully!"]);
            } catch (Exception $e) {
                echo json_encode(["status" => "error", "message" => $e->getMessage()]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Missing emp_id"]);
        }
    }

    private function handleGetEmployee($emp_id) {
        if ($emp_id) {
            try {
                $employee = $this->employee->getEmployeeById($emp_id);
                if ($employee) {
                    $this->sendResponse(["success" => true, "data" => $employee]);
                } else {
                    $this->sendResponse(["success" => false, "error" => "Employee not found."]);
                }
            } catch (Exception $e) {
                $this->sendResponse(["success" => false, "error" => $e->getMessage()]);
            }
        } else {
            $this->sendResponse(["success" => false, "error" => "Missing emp_id."]);
        }
    }

    private function sendResponse($response) {
        echo json_encode($response);
        exit;
    }
}

// Instantiate the EmployeeController and handle the request
$employeeController = new EmployeeController($conn);
$employeeController->handleRequest();