<?php
session_start();
require_once '../models/FitnessClass.php';
require_once '../models/User.php';
require_once '../app_config/operatiiDB.php';

// Verifica autentificarea clientului
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    header('Location: ../auth/login.html');
    exit;
}

$user_id = $_SESSION['user_id'];
$user = User::findById($user_id);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'], $_POST['action'])) {
    header('Content-Type: application/json');
    $class_id = intval($_POST['class_id']);
    $action = $_POST['action'];
    if (!$class_id || !in_array($action, ['enroll', 'unenroll'])) {
        echo json_encode(['success' => false, 'error' => 'Date invalide']);
        exit;
    }

    // Folosește metodele User pentru abonament și înscrieri
    $nr_maxim = $user->getMaxAllowedClasses();
    $inscrieri = $user->getActiveClassRegistrations();
    $nr_inscrieri = count($inscrieri);
    if ($action === 'enroll') {
        if ($nr_inscrieri >= $nr_maxim) {
            echo json_encode(['success' => false, 'error' => 'Limita de clase atinsă pentru abonamentul tău!']);
            exit;
        }
        
        // Verifică dacă s-au atins locurile la clasă
        $nr_inscrisi_clasa = count(OperatiiDB::read('class_registrations', 'WHERE class_id = ?', [$class_id]));
        $clasa = OperatiiDB::read('classes', 'WHERE id = ?', [$class_id]);
        
        if ($clasa && isset($clasa[0]['max_clients'])) {
            $maxim_clasa = (int)$clasa[0]['max_clients'];
        } else {
            $maxim_clasa = 0;
        }

        if ($nr_inscrisi_clasa >= $maxim_clasa) {
            echo json_encode(['success' => false, 'error' => 'Nu mai sunt locuri disponibile la această clasă!']);
            exit;
        }

        if (!$user->isEnrolledToClass($class_id)) {
            OperatiiDB::create('class_registrations', [
                'class_id' => $class_id,
                'client_id' => $user_id
            ]);
        }
        echo json_encode(['success' => true, 'enrolled' => true]);
        exit;
    } elseif ($action === 'unenroll') {
        OperatiiDB::delete('class_registrations', 'class_id = :cid AND client_id = :uid', [':cid' => $class_id, ':uid' => $user_id]);
        echo json_encode(['success' => true, 'enrolled' => false]);
        exit;
    }
}

// Preia toate clasele ca obiecte OOP
$clase = FitnessClass::findAll();
// Preia toți antrenorii într-un array asociativ pentru lookup rapid
$antrenori = [];
$users = OperatiiDB::read('users', 'WHERE role = "trainer"');
foreach ($users as $u) {
    $antrenori[$u['id']] = $u['name'];
}
// Folosește User pentru clasele la care e înscris
$clase_inscris = $user->getActiveClassRegistrations();
?>

<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Client Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center justify-center bg-[url('../imagini/dashboardBG.webp')] bg-cover bg-center py-20">
    <!-- Gray mask overlay -->
    <div class="fixed inset-0 bg-black/40 backdrop-blur-sm z-0"></div>

    <div class="absolute top-0 left-0 w-full flex justify-end items-center py-6 px-8 z-10">
        <a href="../index.php" class="text-left drop-shadow-lg mr-auto no-underline cursor-pointer hover:opacity-90">
            <p class="text-4xl font-extrabold text-white drop-shadow-lg">CipFit</p>
            <p class="text-xl font-semibold text-white drop-shadow-lg">Studio</p>
        </a>
        <a href="chooseSubscription.php"
               class="text-white bg-gray-600 px-5 py-2 rounded-md text-sm font-bold transform transition-all duration-200 hover:scale-110 text-center"
           style="font-family: 'Montserrat', sans-serif;">
            Modifică abonament
        </a>
    </div>

    <div class="relative z-10 flex flex-col items-center min-h-screen pt-32 px-4">
        <h2 class="text-3xl font-extrabold text-white mb-8 drop-shadow-[0_0_5px_black]">
            Toate clasele disponibile :
        </h2>
        <div class="bg-white/20 backdrop-blur-lg p-8 rounded-2xl shadow-xl w-full max-w-7xl border border-white/30">
            <?php if (empty($clase)): ?>
                <p class="text-white font-semibold text-center">Momentan nu există clase disponibile.</p>
            <?php else: ?>
                <table class="w-full text-white border-separate border-spacing-y-4">
                    <thead>
                        <tr>
                            <th class="border-b border-white/50 px-4 py-2 text-left">Titlu</th>
                            <th class="border-b border-white/50 px-4 py-2 text-left">Antrenor</th>
                            <th class="border-b border-white/50 px-4 py-2 text-left">Descriere</th>
                            <th class="border-b border-white/50 px-4 py-2">Data</th>
                            <th class="border-b border-white/50 px-4 py-2">Ora</th>
                            <th class="border-b border-white/50 px-4 py-2">Durată</th>
                            <th class="border-b border-white/50 px-4 py-2">Nr. clienți</th>
                            <th class="border-b border-white/50 px-4 py-2">Locație</th>
                            <th class="border-b border-white/50 px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($clase as $clasa): ?>
                            <tr class="hover:bg-white/10 transition">
                                <td class="align-middle text-center px-2 py-3"><?= htmlspecialchars($clasa->getTitle()) ?></td>
                                <td class="align-middle text-center px-2 py-3"><?= isset($antrenori[$clasa->getTrainerId()]) ? htmlspecialchars($antrenori[$clasa->getTrainerId()]) : '-' ?></td>
                                <td class="align-middle text-center px-2 py-3"><?= htmlspecialchars($clasa->getDescription()) ?></td>
                                <td class="align-middle text-center px-2 py-3"><?= htmlspecialchars($clasa->getDate()) ?></td>
                                <td class="align-middle text-center px-2 py-3"><?= htmlspecialchars($clasa->getTime()) ?></td>
                                <td class="align-middle text-center px-2 py-3"><?= htmlspecialchars($clasa->getDuration()) ?></td>
                                <?php
                                    $nr_inscrisi = count(OperatiiDB::read('class_registrations', 'WHERE class_id = ?', [$clasa->getId()]));
                                    $maxim = (int)$clasa->getMaxClients();
                                    $full = $nr_inscrisi >= $maxim;
                                ?>
                                <td class="align-middle text-center px-2 py-3"><?= $nr_inscrisi ?>/<?= $maxim ?></td>
                                <td class="align-middle text-center px-2 py-3"><?= htmlspecialchars($clasa->getLocation()) ?></td>
                                <td class="align-middle text-center px-2 py-3">
                                    <?php if (in_array($clasa->getId(), $clase_inscris)): ?>
                                        <button type="button" class="bg-red-600 text-white font-bold py-2 px-5 ml-6 rounded-lg hover:scale-105 transition inscriere-btn" data-class-id="<?= (int)$clasa->getId() ?>" data-enrolled="1">Renunță</button>
                                    <?php elseif ($full): ?>
                                        <button type="button" class="bg-gray-400 text-white font-bold py-2 px-5 ml-6 rounded-lg inscriere-btn cursor-not-allowed" data-class-id="<?= (int)$clasa->getId() ?>" data-enrolled="0" disabled>Complet</button>
                                    <?php else: ?>
                                        <button type="button" class="bg-green-600 text-white font-bold py-2 px-5 ml-6 rounded-lg hover:scale-105 transition inscriere-btn" data-class-id="<?= (int)$clasa->getId() ?>" data-enrolled="0">Înscrie-te</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <script>
                    document.querySelectorAll('.inscriere-btn').forEach(function(btn) {
                        btn.addEventListener('click', function() {
                            var classId = btn.getAttribute('data-class-id');
                            var enrolled = btn.getAttribute('data-enrolled') === '1';
                            btn.disabled = true;
                            fetch(window.location.pathname, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded'
                                    },
                                    body: 'class_id=' + encodeURIComponent(classId) + '&action=' + (enrolled ? 'unenroll' : 'enroll')
                                })
                                .then(r => r.json())
                                .then(data => {
                                    if (data.success) {
                                        if (data.enrolled) {
                                            btn.textContent = 'Renunță';
                                            btn.classList.remove('bg-green-600');
                                            btn.classList.add('bg-red-600');
                                            btn.setAttribute('data-enrolled', '1');
                                        } else {
                                            btn.textContent = 'Înscrie-te';
                                            btn.classList.remove('bg-red-600');
                                            btn.classList.add('bg-green-600');
                                            btn.setAttribute('data-enrolled', '0');
                                        }
                                    } else {
                                        alert(data.error || 'Eroare la înscriere/renunțare.');
                                    }
                                    btn.disabled = false;
                                })
                                .catch(() => {
                                    btn.disabled = false;
                                    alert('Eroare de rețea!');
                                });
                        });
                    });
                </script>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>