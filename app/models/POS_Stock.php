<?php 
    class POS_STOCK{
        private $connection;

        public function __construct($db_connection){
            $this->connection = $db_connection;
        }

        public function createStockTransferRecord($stockTransferId, $transferringBranch, $transferredBranch, $issuerName, $numberOfItems) {
            $currentDate = date("Y-m-d");
            $query = "INSERT INTO stock_transfer_records (stock_transfer_id, transferring_branch, transferred_branch, issue_date, issuer_name, number_of_items)
                      VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("sssssi", $stockTransferId, $transferringBranch, $transferredBranch, $currentDate, $issuerName, $numberOfItems);
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }


        public function addStockTransferItem($stockTransferId, $itemName, $itemBarcode, $numOfQty, $stockId) {
            $query = "INSERT INTO stock_transfer_items (stock_transfer_id, item_name, item_barcode, num_of_qty, stock_id)
                      VALUES (?, ?, ?, ?, ?)";
        
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                return false;
            }
        
            $stmt->bind_param("sssis", $stockTransferId, $itemName, $itemBarcode, $numOfQty, $stockId);
        
            if ($stmt->execute()) {
                return true;
            } else {
                return false;
            }
        }

        public function getStockTransferRecordsByTransferringBranch($branch) {
            $query = "SELECT * FROM stock_transfer_records WHERE transferring_branch = ? ORDER BY issue_date DESC";
        
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                return false;
            }
        
            $stmt->bind_param("s", $branch);
            $stmt->execute();
        
            $result = $stmt->get_result();
            $records = [];
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }
        
            $stmt->close();       
            return $records;
        }
        
        public function getStockTransferRecordsByTransferredBranch($branch) {
            $query = "SELECT * FROM stock_transfer_records WHERE transferred_branch = ? ORDER BY issue_date DESC";
        
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                return false;
            }

            $stmt->bind_param("s", $branch);
            $stmt->execute();
        
            $result = $stmt->get_result();
            $records = [];
            while ($row = $result->fetch_assoc()) {
                $records[] = $row;
            }

            $stmt->close();
            return $records;
        }

        public function getStockTransferItemsByStockTransferId($stockTransferId) {
            $query = "SELECT * FROM stock_transfer_items WHERE stock_transfer_id = ?";
            
            $stmt = $this->connection->prepare($query);
            if (!$stmt) {
                return false;
            }
        
            $stmt->bind_param("s", $stockTransferId);
            $stmt->execute();
        
            $result = $stmt->get_result();
            $items = [];
            while ($row = $result->fetch_assoc()) {
                $items[] = $row;
            }
        
            $stmt->close();
            return $items;
        }
        
    }
?>