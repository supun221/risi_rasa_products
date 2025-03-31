<?php
require_once '../models/Damage.php';
require_once '../../config/databade.php';

class DamageController
{

    private $damage;

    public function __construct($conn)
    {
        $this->damage = new Damage($conn);
    }
  //barcode reading 
  public function getitemseByBarcode($barcode)
  {
      try {
          $result = $this->damage->getitemseByBarcode($barcode);
  
          if ($result) {
              return ['status' => 'success', 'data' => $result];
          } else {
              return ['status' => 'error', 'message' => 'Product not found.'];
          }
      } catch (Exception $e) {
          return ['status' => 'error', 'message' => $e->getMessage()];
      }
  }
  
    // Add a new damage
    public function addDamage($data)
    {
        try {
            // Check if the stock is sufficient
            $stockDetails = $this->damage->getitemseByBarcode($data['barcode']);
            if ($stockDetails['available_stock'] < $data['damage_quantity']) {
                return ['status' => 'error', 'message' => 'Insufficient stock for the specified damage quantity.'];
            }
    
            // Add damage and update stock
            $this->damage->addDamage(
                $data['product_name'],
                $data['damage_description'],
                $data['damage_quantity'],
                $data['damage_price'],
                $data['barcode']
            );
    
            return ['status' => 'success', 'message' => 'Damage item added and stock updated successfully.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    

    // Get all damage items
    public function getAllDamageItems()
    {
        try {
            $result = $this->damage->getDamages();
            return $result;
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
    //get damage id 
    public function getDamageById($id)
    {
        try {
            $result = $this->damage->getDamageById($id);

            if ($result) {
                return ['status' => 'success', 'data' => $result];
            } else {
                return ['status' => 'error', 'message' => 'Damage item not found.'];
            }
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
  

    //update damage
    public function updateDamage($id, $data)
    {
        try {
            // Retrieve the current stock details using the barcode
            $stockDetails = $this->damage->getitemseByBarcode($data['barcode']);
            if (!$stockDetails) {
                return ['status' => 'error', 'message' => 'Product not found for the given barcode.'];
            }
    
            // Check if there is sufficient stock to handle the quantity change
            $currentDamage = $this->damage->getDamageById($id);
            if (!$currentDamage) {
                return ['status' => 'error', 'message' => 'Damage item not found.'];
            }
    
            $currentDamageQuantity = $currentDamage['damage_quantity'];
            $newDamageQuantity = $data['damage_quantity'];
            $quantityDifference = $newDamageQuantity - $currentDamageQuantity;
    
            if ($stockDetails['available_stock'] - $quantityDifference < 0) {
                return ['status' => 'error', 'message' => 'Insufficient stock to handle the quantity change.'];
            }
    
            // Perform the update
            $this->damage->updateDamage(
                $id,
                $data['product_name'],
                $data['damage_description'],
                $newDamageQuantity,
                $data['damage_price'],
                $data['barcode']
            );
    
            return ['status' => 'success', 'message' => 'Damage item updated successfully and stock adjusted.'];
        } catch (Exception $e) {
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    
    // Delete a damage item
    public function deleteDamage($id)
    {
        try {
            $this->damage->deleteDamage($id);
            return ['status' => 'success', 'message' => 'Damage item deleted successfully'];
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }
}
//handle http request

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new DamageController($conn);
    $data = json_decode(file_get_contents("php://input"), true);



    // If JSON decoding failed, fallback to form data
    if (json_last_error() !== JSON_ERROR_NONE) {
        $data = $_POST;
    }

    $action = $data['action'] ?? null;
    $id = $data['id'] ?? null;
    $query = $data['query'] ?? null;
    $response = ['status' => 'error', 'message' => 'Invalid action.'];

    switch ($action) {
        case 'add':
            $response = $controller->addDamage($data);
            break;
        case 'get':
            $response = $controller->getAllDamageItems();
            break;

        case 'get_by_barcode':
            $response = $controller->getitemseByBarcode($query);
            break;

        case 'get_by_id':
            $response = $controller->getDamageById($id);
            break;

        case 'update':
            $response = $controller->updateDamage($id, $data);
            break;
        case 'delete':
            $response = $controller->deleteDamage($id);
            break;
    }
    //return json responce
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
