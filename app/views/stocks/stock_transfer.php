<?php
include '../../models/Database.php';
include '../../models/POS_Stock.php';

$posStock = new POS_STOCK($db_conn);

session_start();
$user_name = $_SESSION['username'];
$user_role = $_SESSION['job_role'];
$user_branch = $_SESSION['store'];

if(!$user_name && !$user_branch){
    header("Location: ../unauthorized/unauthorized_access.php");
    exit();
}

if($user_role === 'admin'){
    $user_emblem = '<i class="fa-solid fa-user-tie" class="st-user-profile"></i>';
}else{
    $user_emblem = '<i class="fa-solid fa-circle-user"  class="st-user-profile"></i>';
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transfer</title>
    <link rel="stylesheet" href="./stock_transfer.styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../../assets/notifier/style.css">
    </link>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <script src="../../assets/notifier/index.var.js"></script>
    <script src="../../assets/js/stock_transfer_handler.js"></script>
</head>
<body>

<div class="stock-transfer-page-container">
    <!-- upper ribbon holds currenty signed in user information -->
    <div class="st-upper-ribbon">
        <div class="st-page-title">
            <span class="st-title-main">Eggland Super</span>
            <span class="st-title-sub">Stock Transferring</span>
        </div>

        <div class="st-user-info">
            <div class="st-user-profile">
                <?php echo $user_emblem ?>
            </div>
            <div class="st-user-details">
                <span class="st-user-name">
                    <?php echo $user_name ?>
                </span>
                <span class="st-user-role">
                    <?php echo $user_role ?>
                </span>
            </div>
        </div>
    </div>

    <!-- stock transfer rejecting modal -->
     <div class="st-rejection-modal">
        <button class="st-rejection-close-btn" onclick="hideRejectTransferModal()">
            <i class="fa-solid fa-xmark"></i>
        </button>
        <input type="text" id="rejecting-record-id" hidden>
        <input type="text" id="rejecting-record-barcode" hidden>
        <input type="text" id="rejecting-record-stockid" hidden>
        <input type="text" id="rejecting-record-qty" hidden>
        <span class="st-rejection-heading">Reject Stock Transfer</span>
        <p class="st-rejection-prompt">You are about to reject the selected transfer items. 
            before proceed further, please specify a valid reason for reject this item.</p>
        <textarea id="st-rejetion-note"></textarea>
        <button class="st-rejection-btn" onclick="rejectStockTransfer()">Reject Transfer</button>
     </div>

    <!-- stock operations-container -->
     <div class="stock-ops-container">
        <!-- transferred stocks holder -->
        <div class="stocks-container transferred-stocks" id="transferred-stocks">
            <span class="st-partition-headline">Transferred Stocks</span>

            <div id="transferred-stocks-pholder">

            </div>
        </div>
        <!-- received stocks holder -->
        <div class="stocks-container received-stocks" id="received-stocks">
            <span class="st-partition-headline">Received Stocks</span>

            <div id="received-stocks-pholder">
                
            </div>
        </div>
     </div>

     <!-- floating buttons -->
      <div class="floating-btn-container">
        <button class="floating-btn">
            <a href="#transferred-stocks">
                <i class="fa-solid fa-box-open"></i>
            </a>
        </button>
        <button class="floating-btn">
            <a href="#received-stocks">
                <i class="fa-solid fa-boxes-stacked"></i>
            </a>
        </button>
        <button class="floating-btn" onclick="showStockTransferModal()">
            <i class="fa-solid fa-plus"></i>
        </button>
      </div>

      <!-- modal transfer order -->
       <div id="transfer-order-modal" class="transfer-order-modal">
           
           <div class="transfer-modal-form-area">
                <button class="st-modal-close" onclick="hideStockTransferModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <span class="st-partition-headline">Create Stock Transfer Order</span>

                <!-- item scanner row -->
                <div class="barcode-reader-container">
                    <table id="barcode-reader-tb">
                        <thead class="thead-dark">
                            <tr>
                                <th>Branch</th>
                                <th>Barcode</th>
                                <th>Product Name</th>
                                <th>Quantity</th>
                            </tr>
                        </thead>
                        <tbody id="barcode-reader-items">
                            <tr>
                                <td>
                                    <select type="text" id="branch_selector" class="branch_selector" >
                                        <?php 
                                            $sql = "SELECT branch_id, branch_name FROM branch";
                                            $result = $db_conn->query($sql);
                                        
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    echo "<option value='" . htmlspecialchars($row["branch_name"]) . "'>" . htmlspecialchars($row["branch_name"]) . "</option>";
                                                }
                                            } else {
                                                echo "<option value='' disabled>No branches available</option>";
                                            }
                                            $db_conn->close();
                                        ?>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" id="barcode-input" class="barcode-input" placeholder="Enter barcode">
                                </td>
                                <td>
                                    <input type="text" id="product-name" class="barcode-product-name" readonly>
                                </td>
                                <td>
                                    <input type="number" id="quantity" class="barcode-quantity" value="1" >
                                </td>
                                <td>
                                    <button class="add-product-to-cart" onclick="addToCartFromInput()">Add to Cart</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Modal for Selecting Stock -->
                <div id="stock-selection-modal" class="modal" style="display: none;">
                    <div class="stock-selection-modal-content">
                        <h3>Select a Stock</h3>
                        <table id="stock-selection-table">
                            <thead>
                                <tr>
                                    <th>Stock ID</th>
                                    <th>Product Name</th>
                                    <th>Available Stock</th>
                                    <th>Cost Price</th>
                                </tr>
                            </thead>
                            <tbody id="stock-selection-body"></tbody>
                        </table>
                    
                    </div>
                </div>


                <!-- transferring items displayer -->
                 <div class="transferring-item-displayer">
                        <table class="table table-striped table-hover item-cart" id="st-cart-tb">
                            <thead class="thead-dark">
                                <tr>
                                    <th>No.</th>
                                    <th>Stock ID</th>
                                    <th>Barcode</th>
                                    <th>Product Information</th>
                                    <th>Our Price</th>
                                    <th>QTY</th>
                                    <th style="width: 30px;"></th>
                                </tr>
                            </thead>

                            <!-- tbody -->
                            <tbody id="st-cart-items">

                            </tbody>
                        </table>
                 </div>

                 <!-- stock transfer options btns -->
                  <div class="st-btn-container">
                        <button class="st-transfer-btn" onclick="submitStockTransfer()">
                            Create Stock Transfer
                        </button>
                  </div>
            </div>
       </div>
</div>

</body>
<script>  document.addEventListener("keydown", function(event) {
        if (event.code === "Home") {
            window.location.href = "../dashboard/index.php";
        }
    });
</script>
</html>