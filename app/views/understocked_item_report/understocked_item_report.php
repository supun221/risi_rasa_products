<?php
    include '../../models/Database.php';
    include '../../models/POS_Product.php';
    $productHandler = new POS_PRODUCT($db_conn);
    $products = $productHandler->getUnderstockedItems();
    $currentDate = date('Y/m/d')
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="images/logo.png">
    <title>Eggland Super</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Maname&family=Noto+Serif:ital,wght@0,100..900;1,100..900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Yaldevi:wght@200..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 100%;
        }
        .report-body {
            width: 100%;
            max-width: 920px;
            display: block;
            margin: 0 auto;
            padding: 10px;
        }
        
        .print-button{
            position: absolute;
            bottom: 20px;
            right: 100px;
            border: none;
            background-color: #004080;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px 20px;
            border-radius: 10px;
        }

        .print-button > i {
            margin-right: 10px;
        }

        .print-button:hover{
            cursor: pointer;
        }

        .table-container{
            margin-top: 20px;
        }

        .report-headline{
            text-align: center;
            font-family: "Noto Sans" , serif;
            font-size: 3em;
            font-weight: 500;
            color: #2980b9;
        }

        .report-title{
            font-family: "Poppins" , serif;
            font-size: 1.2em;
            text-align: center;
            color: #004080;
        }

        .date{
            font-family: "Poppins" , serif;
            text-align: center;
            color: grey;
        }

        th:nth-child(1){
            background-color: #2980b9 !important;
             text-align:center; 
             color:white; 
             font-size: 1.2em;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }

        @media print{
            .print-button{
                display: none;
            }

            table, th, tr, td{
                -webkit-print-color-adjust: exact;
            }

            table {
                border: none !important;
            }
            
            tr th:nth-child(1){
                border: none !important;
            }

            th{
                border: none !important;
            }

            th:nth-child(1){
                background-color: #2980b9 !important;
                text-align:center; 
                color:white; 
                font-size: 1.2em;
                border: none;
            }

        }
    </style>
</head>
<body>

<div class="report-body">
    <h1 class="report-headline">Eggland Super</h1>
    <h4 class="report-title">Understocked Items Detailed Report</h4>
    <h6 class="date"><?php echo $currentDate ?></h6>

    <!-- invoice body -->
    <hr style="border-top: 1px solid #004080;">

    <button class="print-button" onclick="window.print()"> <i class="fa-solid fa-print"></i> Print Invoice</button>

    <div class="table-container">
        <table>
            <tr style="border: none;">
                <th style="min-width: 300px;">Product Name</th>
                <th style="text-align: center; background-color:#004080; color: white;">Bar Code</th>
                <th style="text-align: center; background-color:#004080; color: white;">Current Stock</th>
                <th style="text-align: center; background-color:#004080; color: white;">Max Retail Price</th>
                <th style="text-align: center; background-color:#004080; color: white;">Wholesale Price</th>
                <th style="text-align: center; background-color:#004080; color: white;">Our Price</th>
            </tr>
            <?php
                if(count($products) > 0){
                    foreach ($products as $productRow) {
                        echo "
                        <tr>
                            <td style='text-transform: capitalize;'>{$productRow['product_name']}</td>
                            <td style='text-align:center;'>{$productRow['barcode']}</td>
                            <td style='text-align:center;'>{$productRow['available_stock']}</td>
                            <td style='text-align:center;'>". number_format($productRow['max_retail_price'], 2) ."</td>
                            <td style='text-align:center;'>". number_format($productRow['wholesale_price'], 2) ."</td>
                            <td style='text-align:center;'>". number_format($productRow['our_price'], 2) ."</td>
                        </tr>";
                    }
                }else{
                    echo "
                        <tr>
                            <td colspan='6'>No understocked items currently!</td>
                        </tr>";
                }
            ?>
        </table>
    </div>

    </div>
</div>
</body>
</html>
