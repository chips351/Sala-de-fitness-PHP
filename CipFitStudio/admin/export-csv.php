<?php
require_once '../app_config/operatiiDB.php';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="statistici-inscrieri.csv"');

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
// Output CSV
$output = fopen('php://output', 'w');
fputcsv($output, ['Ziua', 'Data', 'Numar inscrieri']);
for ($i = 0; $i < 7; $i++) {
    fputcsv($output, [$labels[$i], $dates[$i], $inscrieri[$i]]);
}
fclose($output);
