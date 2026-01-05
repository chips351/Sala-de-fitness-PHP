<?php
require_once '../app_config/operatiiDB.php';
define('FPDF_FONTPATH', __DIR__ . '/../app_config/font/');
require_once '../app_config/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Statistici inscrieri ultimele 7 zile',0,1,'C');
$pdf->Ln(5);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(40,10,'Ziua',1);
$pdf->Cell(40,10,'Data',1);
$pdf->Cell(50,10,'Numar inscrieri',1);
$pdf->Ln();

$zile = ['Luni', 'Marti', 'Miercuri', 'Joi', 'Vineri', 'Sambata', 'Duminica'];
$inscrieri = array_fill(0, 7, 0);
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}
$conn = Database::getInstance()->getConnection();
$sql = "SELECT DATE(registered_at) as zi, COUNT(*) as total FROM class_registrations WHERE DATE(registered_at) >= ? GROUP BY zi";
$stmt = $conn->prepare($sql);
$stmt->execute([$dates[0]]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $row) {
    $zi = $row['zi'];
    $poz = array_search($zi, $dates);
    if ($poz !== false) {
        $inscrieri[$poz] = (int)$row['total'];
    }
}
$labels = [];
foreach ($dates as $d) {
    $nr = date('N', strtotime($d));
    $labels[] = $zile[$nr-1];
}
$pdf->SetFont('Arial','',12);
for ($i = 0; $i < 7; $i++) {
    $pdf->Cell(40,10,$labels[$i],1);
    $pdf->Cell(40,10,$dates[$i],1);
    $pdf->Cell(50,10,$inscrieri[$i],1);
    $pdf->Ln();
}
$pdf->Output('D', 'statistici-inscrieri.pdf');
