<?php
require_once '../app_config/operatiiDB.php';

header('Content-Type: application/json');

$zile = ['Luni', 'Marți', 'Miercuri', 'Joi', 'Vineri', 'Sâmbătă', 'Duminică'];

$inscrieri = array_fill(0, 7, 0);

// calculeaza datele pentru ultimele 7 zile
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}

// query: nr inscrieri la clase pe fiecare zi (din class_registrations)
$conn = Database::getInstance()->getConnection();
$sql = "SELECT DATE(registered_at) as zi, COUNT(*) as total FROM class_registrations WHERE DATE(registered_at) >= ? GROUP BY zi";
$stmt = $conn->prepare($sql);
$stmt->execute([$dates[0]]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// asociere rezultate pe zile
foreach ($rows as $row) {
    $zi = $row['zi'];
    $poz = array_search($zi, $dates);
    if ($poz !== false) {
        $inscrieri[$poz] = (int)$row['total'];
    }
}

$labels = [];//pt afisarea zilelor saptamanii, transform fiecare zi in numele zilei
foreach ($dates as $d) {
    $nr = date('N', strtotime($d)); // 1=Luni .. 7=Duminica
    $labels[] = $zile[$nr-1];
}
$response = [
    'labels' => $labels,
    'data' => $inscrieri
];
echo json_encode($response);
