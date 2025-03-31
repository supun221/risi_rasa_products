<?php
require_once '../models/Salesman.php';
require_once '../../config/databade.php';

class SalesmanController{
    private $salesman;
    
    public function __construct($conn){
        $this->salesman = new Salesman($conn);
    }
    
    // Add a new salesman
    public function addSalesman($data){
        try {
            // if($this->salesman->salesmanExists($data['id'])){
            //     return ['status' => 'error','message' => 'Salesman with this ID already exists.'];
            // }
            
            $this->salesman->createSalesman( $data['name'], $data['telephone'], $data['nic'], $data['address']);
            return ['status' => 'success','message' => 'Salesman added successfully.'];
        } catch(Exception $e){
            return ['status' => 'error','message' => $e->getMessage()];
        }
    }
    
    // Get salesman details by ID
    public function getSalesman($id){
        try {
            $salesman = $this->salesman->getSalesmanById($id);
            if(!$salesman){
                return ['status' => 'error','message' => 'Salesman not found.'];
            }
            return ['status' => 'success','data' => $salesman];
        } catch(Exception $e){
            return ['status' => 'error','message' => $e->getMessage()];
        }
    }
    
    // Update salesman details
    public function updateSalesman($id, $data){
        try {
            if(!$this->salesman->salesmanExists($id)){
                return ['status' => 'error','message' => 'Salesman not found.'];
            }
            
            $this->salesman->updateSalesman($id, $data['name'], $data['telephone'], $data['nic'], $data['address']);
            return ['status' => 'success','message' => 'Salesman updated successfully.'];
        } catch(Exception $e){
            return ['status' => 'error','message' => $e->getMessage()];
        }
    }
    
    // Delete a salesman
    public function deleteSalesman($id){
        try {
            if(!$this->salesman->salesmanExists($id)){
                return ['status' => 'error','message' => 'Salesman not found.'];
            }
            
            $this->salesman->deleteSalesman($id);
            return ['status' => 'success','message' => 'Salesman deleted successfully.'];
        } catch(Exception $e){
            return ['status' => 'error','message' => $e->getMessage()];
        }
    }

}

//handle http 

if($_SERVER['REQUEST_METHOD']='POST'){
    require_once '../../config/databade.php';
    $controller = new SalesmanController($conn);

     // Check if the request is JSON
     $input = file_get_contents("php://input");
     $data = json_decode($input, true);
 
     // If JSON decoding failed, fallback to form data
     if (json_last_error() !== JSON_ERROR_NONE) {
         $data = $_POST;
     }
     $action = $data['action'] ?? null;
     $id = $data['id'] ?? null;
     $response = ['status' => 'error', 'message' => 'Invalid action.'];

     switch($action){
         case 'add':
             $response = $controller->addSalesman($data);
             break;
         case 'update':
             if(!isset($data['id'])){
                 $response = ['status' => 'error','message' => 'ID is required for update.'];
             } else {
                 $response = $controller->updateSalesman($data['id'], $data);
             }
            //  $response = $controller->updateSalesman($id, $data);
             break;
         case 'get':
             if(!isset($data['id'])){
                 $response = ['status' => 'error','message' => 'ID is required for get.'];
             } else {
                 $response = $controller->getSalesman($data['id']);
             }
            // $response = $controller->getSalesman($id);
             break;
         case 'delete':
             if(!$id){
                 $response = ['status' => 'error','message' => 'ID is required for delete.'];
             } else {
                 $response = $controller->deleteSalesman($id);
             }
             //$response = $controller->deleteSalesman($id);
             break;
         default:
             $response = ['status' => 'error','message' => 'Invalid action.'];
     }
     header('Content-Type: application/json');
     echo json_encode($response);
}