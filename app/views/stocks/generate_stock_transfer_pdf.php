<?php
require('fpdf.php');

if (isset($_GET['stock_transfer_id'])) {
    $stockTransferId = $_GET['stock_transfer_id'];

    // Database Connection (Replace with your actual connection details)
    include('database_connection.php');
    
    // Fetch stock transfer details
    $query = "SELECT * FROM stock_transfer_records WHERE stock_transfer_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$stockTransferId]);
    $transfer = $stmt->fetch();

    if (!$transfer) {
        die("Invalid stock transfer ID");
    }

    // Initialize PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, "Stock Transfer Issue #{$stockTransferId}", 0, 1, 'C');
    $pdf->Ln(10);

    // Transfer Details
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 10, "Issue Date: " . $transfer['issue_date'], 0, 1);
    $pdf->Cell(190, 10, "From Branch: " . $transfer['transferring_branch'], 0, 1);
    $pdf->Cell(190, 10, "To Branch: " . $transfer['transferred_branch'], 0, 1);
    $pdf->Cell(190, 10, "Issued By: " . $transfer['issuer_name'], 0, 1);
    $pdf->Ln(10);

    // Table Header
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(80, 10, "Item Name", 1);
    $pdf->Cell(50, 10, "Barcode", 1);
    $pdf->Cell(30, 10, "Quantity", 1);
    $pdf->Ln();

    // Fetch transfer items
    $query = "SELECT * FROM stock_transfer_items WHERE stock_transfer_id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$stockTransferId]);
    $items = $stmt->fetchAll();

    $pdf->SetFont('Arial', '', 12);
    foreach ($items as $item) {
        $pdf->Cell(80, 10, $item['item_name'], 1);
        $pdf->Cell(50, 10, $item['item_barcode'], 1);
        $pdf->Cell(30, 10, $item['num_of_qty'], 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output();
}
?>
