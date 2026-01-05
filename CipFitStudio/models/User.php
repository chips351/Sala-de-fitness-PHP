<?php
require_once __DIR__ . '/../app_config/operatiiDB.php';

class User
{
    // Gaseste user după id
    public static function findById($id)
    {
        $result = OperatiiDB::read('users', 'WHERE id = ?', [$id]);
        return $result ? new User($result[0]) : null;
    }
    // Returnează abonamentul activ (sau null)
    public function getActiveSubscription()
    {
        $abonamente = OperatiiDB::read('subscriptions', 'WHERE user_id = ? AND status = "active" AND end_date >= CURDATE()', [$this->id]);
        return $abonamente ? $abonamente[0] : null;
    }

    // Returnează limita de clase în funcție de abonament
    public function getMaxAllowedClasses()
    {
        $abonament = $this->getActiveSubscription();
        if (!$abonament) return 0;
        switch ($abonament['type']) {
            case 'Basic':
                return 1;
            case 'Premium':
                return 2;
            case 'VIP':
                return 3;
            default:
                return 0;
        }
    }

    // Returnează toate clasele la care userul e înscris (array de id-uri)
    public function getActiveClassRegistrations()
    {
        $inscrieri = OperatiiDB::read('class_registrations', 'WHERE client_id = ?', [$this->id]);
        return array_column($inscrieri, 'class_id');
    }

    // Verifică dacă userul e înscris la o anumită clasă
    public function isEnrolledToClass($classId)
    {
        $inscrieri = $this->getActiveClassRegistrations();
        return in_array($classId, $inscrieri);
    }
    private $id;
    private $name;
    private $username;
    private $email;
    private $pending_email;
    private $password;
    private $role;
    private $phone;
    private $status;
    private $account_activation_hash;

    public function __construct($data = [])
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->pending_email = $data['pending_email'] ?? null;
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->status = $data['status'] ?? 'active';
        $this->account_activation_hash = $data['account_activation_hash'] ?? null;
    }

    // getteri
    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getPendingEmail()
    {
        return $this->pending_email;
    }
    public function getPassword()
    {
        return $this->password;
    }
    public function getRole()
    {
        return $this->role;
    }
    public function getPhone()
    {
        return $this->phone;
    }
    public function getStatus()
    {
        return $this->status;
    }
    public function getAccountActivationHash()
    {
        return $this->account_activation_hash;
    }

    // Validare pentru editare profil (fără username/rol)
    public function validateEditProfile($nume, $email, $telefon, $parola, $parolaNoua, $parolaCurentaHash)
    {
        $errors = [];
        if (empty(trim($nume))) {
            $errors[] = 'Numele este obligatoriu.';
        }
        if (empty(trim($email))) {
            $errors[] = 'Email-ul este obligatoriu.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email-ul nu este valid.';
        }
        if (empty(trim($telefon))) {
            $errors[] = 'Telefonul este obligatoriu.';
        }
        // Parola este obligatorie la orice modificare
        if (empty($parola)) {
            $errors[] = 'Parola actuală este obligatorie pentru a salva modificările!';
        } else if (!password_verify($parola, $parolaCurentaHash)) {
            $errors[] = 'Parola actuală este greșită!';
        }
        if ($parolaNoua && strlen($parolaNoua) < 6) {
            $errors[] = 'Parola nouă trebuie să aibă minim 6 caractere!';
        }
        if ($parola && $parolaNoua === '') {
            // Nu adăuga eroare dacă nu vrea să schimbe parola, doar dacă a completat ceva la parola nouă
        }
        return $errors;
    }

    public function setAccountActivationHash($hash)
    {
        $this->account_activation_hash = $hash;
    }

        public function setName($name)
    {
        $this->name = $name;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }
    public function setPendingEmail($pending_email)
    {
        $this->pending_email = $pending_email;
    }

    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }
 
    // Salveaza modificarile in db
    public function save()
    {
        $result = OperatiiDB::update('users', [
            'name' => $this->name,
            'email' => $this->email,
            'pending_email' => $this->pending_email,
            'phone' => $this->phone,
            'account_activation_hash' => $this->account_activation_hash,
            'password' => $this->password
        ], 'id = :id', [':id' => $this->id]);
        return $result > 0; //update returneaza nr de randuri afectate
    }

    public function create($unhashedPassword, $confirmPassword)
    {
        $errors = $this->validate($unhashedPassword, $confirmPassword);
        if (!empty($errors)) {
            throw new Exception($errors[0]);
        }

        $this->password = password_hash($unhashedPassword, PASSWORD_DEFAULT);

        $id = OperatiiDB::create('users', [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'phone' => $this->phone,
            'status' => $this->status,
            'account_activation_hash' => $this->account_activation_hash
        ]); //OperatiiDB::create returneaza ultimul ID inserat

        //si apoi il atribuim obiectului
        $this->id = $id;
        return $id;
    }

    public static function emailExists($email)
    {
        $result = OperatiiDB::read(
            'users',
            'WHERE email = :email',
            [':email' => $email]
        );
        return !empty($result);
    }

    public static function findByUsername($username)
    {
        $result = OperatiiDB::read(
            'users',
            'WHERE username = :username',
            [':username' => $username]
        );

        return $result ? new User($result[0]) : null;
    }

    public function verifyPassword($unhashedPassword)
    {
        return password_verify($unhashedPassword, $this->password);
    }

    private function usernameExists($username)
    {
        $result = OperatiiDB::read(
            'users',
            'WHERE username = :username',
            [':username' => $username]
        );
        return !empty($result);
    }

    public function validate($unhashedPassword, $confirmPassword)
    {
        $errors = [];

        if (empty(trim($this->name))) {
            $errors[] = "Numele este obligatoriu.";
        }

        if (empty(trim($this->username))) {
            $errors[] = "Username-ul este obligatoriu.";
        }

        if (empty(trim($this->email))) {
            $errors[] = "Email-ul este obligatoriu.";
        } elseif (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email-ul nu este valid.";
        }

        if (empty(trim($this->role))) {
            $errors[] = "Rolul este obligatoriu.";
        }

        if (empty($unhashedPassword)) {
            $errors[] = "Parola este obligatorie.";
        } elseif (strlen($unhashedPassword) < 6) {
            $errors[] = "Parola trebuie să aibă cel puțin 6 caractere.";
        }

        if ($unhashedPassword !== $confirmPassword) {
            $errors[] = "Parolele nu coincid!";
        }

        // Verificare username unic
        if ($this->usernameExists($this->username)) {
            $errors[] = "Username-ul '{$this->username}' există deja!";
        }

        // Verificare email unic
        if ($this->emailExists($this->email)) {
            $errors[] = "Email-ul '{$this->email}' există deja!";
        }

        return $errors;
    }
}
