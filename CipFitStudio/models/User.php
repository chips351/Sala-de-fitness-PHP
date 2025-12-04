<?php
require_once __DIR__ . '/../app_config/operatiiDB.php';

class User {
    private $id;
    private $name;
    private $username;
    private $email;
    private $password;
    private $role;
    private $phone;
    private $status;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? '';
        $this->username = $data['username'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->password = $data['password'] ?? '';
        $this->role = $data['role'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->status = $data['status'] ?? 'active';
    }

    // getteri
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getRole() { return $this->role; }
    public function getPhone() { return $this->phone; }
    public function getStatus() { return $this->status; }

    public function create($unhashedPassword, $confirmPassword) {
        $errors = $this->validate($unhashedPassword, $confirmPassword);
        if (!empty($errors)) {
            throw new Exception($errors[0]);
        }

        // username unic
        if ($this->usernameExists($this->username)) {
            throw new Exception("Username-ul '{$this->username}' există deja!");
        }

        $this->password = password_hash($unhashedPassword, PASSWORD_DEFAULT);

        $id = OperatiiDB::create('users', [
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'role' => $this->role,
            'phone' => $this->phone,
            'status' => $this->status
        ]);

        $this->id = $id;
        return $id;
    }

    public static function findByUsername($username) {
        $result = OperatiiDB::read('users',
            'WHERE username = :username',
            [':username' => $username]
        );

        return $result ? new User($result[0]) : null;
    }

    public function verifyPassword($unhashedPassword) {
        return password_verify($unhashedPassword, $this->password);
    }

    private function usernameExists($username) {
        $result = OperatiiDB::read('users', 
            'WHERE username = :username', 
            [':username' => $username]
        );
        return !empty($result);
    }

    private function validate($unhashedPassword, $confirmPassword) {
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

        return $errors;
    }
}
