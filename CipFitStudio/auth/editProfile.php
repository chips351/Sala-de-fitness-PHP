<?php
session_start();
require_once '../models/User.php';
require_once '../app_config/connectDB.php';
require_once '../app_config/Email.php';


if (!isset($_SESSION['user_id'])) {
	header('Location: ../auth/login.html');
	exit();
}
// Nu permite accesul adminului la editare profil
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
	header('Location: ../admin/adminDashboard.php');
	exit();
}

$user = User::findById($_SESSION['user_id']);

// Procesare formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$response = ["success" => false, "message" => "", "errors" => []];
	header('Content-Type: application/json');
	try {
		$data = json_decode(file_get_contents('php://input'), true);
		if (!is_array($data)) {
			$response['errors'][] = 'Date trimise incorect sau formular invalid!';
			throw new Exception('Date trimise incorect sau formular invalid!');
		}
		$nume = trim($data['nume'] ?? '');
		$email = trim($data['email'] ?? '');
		$telefon = trim($data['telefon'] ?? '');
		$parola = $data['parola'] ?? '';
		$parolaNoua = $data['parola_noua'] ?? '';

		// reincarca userul din baza de date pentru a avea datele actualizate (inclusiv emailul confirmat)
		$user = User::findById($_SESSION['user_id']);

		//dacă exista un hash de activare, dar utilizatorul a revenit la emailul original sau emailul a fost activat, elimina tokenul si permite salvarea
		if ($user->getAccountActivationHash() !== null) {
			if (strtolower($email) === strtolower($user->getEmail()) || !empty($_SESSION['account_activated'])) {
				$user->setAccountActivationHash(null);
				$user->setPendingEmail(null);
				$user->save();
				unset($_SESSION['account_activated']);
			} else {
				$response['success'] = false;
				$response['message'] = 'Verifică emailul pentru link-ul de activare înainte de a salva alte modificări!';
				echo json_encode($response);
				exit();
			}
		}
    
		// Daca emailul introdus este diferit de cel din baza de date, trimite email de activare si blocheaza modificarile
		if (strtolower($email) !== strtolower($user->getEmail())) {
			$errors = $user->validateEditProfile($nume, $email, $telefon, $parola, $parolaNoua, $user->getPassword());
			if (!empty($errors)) {
				$response['errors'] = $errors;
				echo json_encode($response);
				exit();
			}
			if (User::emailExists($email)) {
				$response['errors'][] = "Email-ul '$email' există deja!";
				echo json_encode($response);
				exit();
			}
			$user->setName($nume);
			$user->setPhone($telefon);
			$user->setPendingEmail($email);
			$token = bin2hex(random_bytes(16));
			$hash = hash('sha256', $token);
			$user->setAccountActivationHash($hash);
			$user->save();
			Email::sendVerificationEmail($email, $user->getName(), $token);
			$response['success'] = false;
			$response['message'] = 'Verifică emailul pentru link-ul de activare.';
			echo json_encode($response);
			exit();
		}

		$errors = $user->validateEditProfile($nume, $email, $telefon, $parola, $parolaNoua, $user->getPassword());
		if (!empty($errors)) {
			$response['errors'] = $errors;
			throw new Exception('');
		}

		$user->setName($nume);
		$user->setPhone($telefon);
		// Emailul se modifica doar după confirmare, nu aici

		$user->setPendingEmail(null);

		if ($parolaNoua) {
			$user->setPassword(password_hash($parolaNoua, PASSWORD_DEFAULT));
		}
        
		$user->save();
		$response['success'] = true;
		$response['message'] = 'Datele au fost actualizate cu succes!';
	} catch (Exception $e) {
		if ($e->getMessage() && (!in_array($e->getMessage(), $response['errors']))) {
			$response['errors'][] = $e->getMessage();
		}
	}
	echo json_encode($response);
	exit();
}

?>
<!DOCTYPE html>
<html lang="ro">

<head>
	<meta charset="UTF-8">
	<title>Modifică profilul</title>
	<script src="https://cdn.tailwindcss.com"></script>
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
	<style>
		body {
			font-family: 'Montserrat', sans-serif;
		}
	</style>
</head>

<body class="min-h-screen flex justify-center items-center bg-[url('../imagini/dumbells.webp')] bg-cover bg-center py-20">
	<div class="bg-gradient-to-b from-black/50 to-white/50 backdrop-blur-sm shadow-xl rounded-3xl p-10 w-[450px] flex flex-col items-center">
		<a href="../index.php" class="flex items-center gap-6 mb-10 no-underline cursor-pointer hover:opacity-90">
			<img src="../imagini/logo.webp" alt="CipFit logo" class="h-20 w-auto object-contain drop-shadow-lg" />
			<div class="w-[1.3px] h-16 bg-black drop-shadow-lg"></div>
			<div class="text-left drop-shadow-lg">
				<p class="text-4xl font-extrabold text-black drop-shadow-lg">CipFit</p>
				<p class="text-xl font-semibold text-black drop-shadow-lg">Studio</p>
			</div>
		</a>
		<p class="text-2xl font-bold text-black mb-8">Modifică datele personale</p>
		<form id="editProfileForm" method="POST" class="w-full flex flex-col items-center" autocomplete="off">
			<div class="w-full mb-5">
				<label class="block text-gray-700 font-medium mb-1">Nume complet *</label>
				<input type="text" name="nume" value="<?= htmlspecialchars($user->getName()) ?>" class="w-full h-12 px-4 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Ex: Andrei Popescu" />
			</div>
			<div class="w-full mb-5">
				<label class="block text-gray-700 font-medium mb-1">Email *</label>
				<input type="email" name="email" value="<?= htmlspecialchars($user->getEmail()) ?>" class="w-full h-12 px-4 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Introdu email-ul" />
			</div>
			<div class="w-full mb-5">
				<label class="block text-gray-700 font-medium mb-1">Telefon</label>
				<input type="text" name="telefon" value="<?= htmlspecialchars($user->getPhone()) ?>" class="w-full h-12 px-4 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="07xx xxx xxx" />
			</div>
			<div class="w-full mb-5 relative">
				<label class="block text-left text-gray-700 font-medium mb-1">Parola actuală *</label>
				<input type="password" name="parola" class="w-full h-12 px-4 pr-12 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Introdu parola actuală" />
				<button type="button" class="absolute right-3 top-[42px] w-6 h-6 opacity-90" onclick="const i=this.parentElement.querySelector('input'); i.type = i.type === 'password' ? 'text' : 'password';">
					<img src="../imagini/see_password.png" alt="See password" class="w-full h-full object-contain" />
				</button>
			</div>
			<div class="w-full mb-2 relative">
				<label class="block text-left text-gray-700 font-medium mb-1">Parola nouă</label>
				<input type="password" name="parola_noua" class="w-full h-12 px-4 pr-12 bg-white rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-red-500" placeholder="Introdu parola nouă" />
				<button type="button" class="absolute right-3 top-[42px] w-6 h-6 opacity-90" onclick="const i=this.parentElement.querySelector('input'); i.type = i.type === 'password' ? 'text' : 'password';">
					<img src="../imagini/see_password.png" alt="See password" class="w-full h-full object-contain" />
				</button>
			</div>
			<button type="submit" class="w-full bg-black text-white text-lg font-bold py-3 rounded-lg hover:scale-105 transition mt-5 p-3">Salvează modificările</button>
			<div id="message" class="text-red-600 font-medium mt-2 w-full text-center"></div>
		</form>
		<a href="../index.php" class="text-sm text-black mt-4 hover:underline">Înapoi la homepage</a>
		<script>
			const form = document.getElementById('editProfileForm');
			const messageDiv = document.getElementById('message');

			form.addEventListener('submit', function(event) {
				event.preventDefault();
				const formattedFormData = {
					nume: form.nume.value,
					email: form.email.value,
					telefon: form.telefon.value,
					parola: form.parola.value,
					parola_noua: form.parola_noua.value
				};
				postData(formattedFormData);
			});

			async function postData(formattedFormData) {
				try {
					const response = await fetch('', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json'
						},
						body: JSON.stringify(formattedFormData)
					});
					const data = await response.json();
					if (data.success) {
						messageDiv.textContent = data.message;
						messageDiv.className = 'text-green-600 font-bold text-lg mt-2';
						setTimeout(() => {
							window.location.href = '../index.php';
						}, 1500);
					} else if (data.errors && data.errors.length) {
						messageDiv.textContent = data.errors[0];
						messageDiv.className = 'text-red-600 font-medium mt-2';
					} else {
						messageDiv.textContent = data.message || 'Eroare necunoscută';
						messageDiv.className = 'text-red-600 font-medium mt-2';
					}
				} catch (err) {
					messageDiv.className = 'text-red-600 font-medium mt-2';
					messageDiv.textContent = err.message || 'Eroare de rețea';
				}
			}
		</script>
	</div>
</body>

</html>