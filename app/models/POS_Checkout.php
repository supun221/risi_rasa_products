<?php 

    class POS_CHECKOUT{
        private $connection;

        public function __construct($db_connection){
            $this->connection = $db_connection;
        }

       
        public function retrieveAdvancePaymentRecord($customer_id){
            $stmt = $this->connection->prepare("SELECT * FROM advance_payments WHERE customer_id = ? ORDER BY created_at DESC LIMIT 1");
            $stmt->bind_param('i', $customer_id);
            $stmt->execute();

            if($stmt->execute()){
                $results = $stmt->get_result();
                return $results->fetch_assoc();
            }else{
                return "Error executing query";
            }
        }

        public function deleteAdvancedPaymentById($record_id){
            $stmt = $this->connection->prepare("DELETE FROM advance_payments WHERE id = ?");
            $stmt->bind_param('i', $record_id);

            if ($stmt->execute()) {
                return "Record with ID $record_id has been deleted successfully.";
            } else {
                return "Error executing query: " . $stmt->error;
            }
        }

        public function createBillDeletionRecord($bill_id, $reason, $cancelled_by, $bill_amount, $cancelled_date) {
            $stmt = $this->connection->prepare("INSERT INTO deleted_bills (bill_id, cancelled_date, reason, cancelled_by, bill_amount) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssd', $bill_id, $cancelled_date, $reason, $cancelled_by, $bill_amount);
        
            if ($stmt->execute()) {
                return "Bill deletion record for Bill ID $bill_id has been created successfully.";
            } else {
                return "Error executing query: " . $stmt->error;
            }
        }

        public function createDeletedBillItem($bill_id, $item_name, $barcode, $unit_price, $quantity, $total_price, $discount, $bill_type, $deleted_by , $deleted_date) {
            $stmt = $this->connection->prepare("INSERT INTO deleted_bill_items (bill_id, item_name, barcode, unit_price, quantity, total_price, discount, bill_type, deleted_by, deleted_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssiiidsss', $bill_id, $item_name, $barcode, $unit_price, $quantity, $total_price, $discount, $bill_type, $deleted_by, $deleted_date);
        
            if ($stmt->execute()) {
                return json_encode(["status" => "success", "message" => "Deleted bill item added successfully."]);
            } else {
                return json_encode(["status" => "error", "message" => "Error executing query: " . $stmt->error]);
            }
        }
        
        
        
    }
?>