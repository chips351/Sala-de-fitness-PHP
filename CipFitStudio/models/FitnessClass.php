<?php
require_once '../config/operatiiDB.php';

class FitnessClass {
    private $id;
    private $trainerId;
    private $title;
    private $description;
    private $date;
    private $time;
    private $duration;
    private $maxClients;
    private $location;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->trainerId = $data['trainer_id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->description = $data['description'] ?? '';
        $this->date = $data['DATE'] ?? '';
        $this->time = $data['TIME'] ?? '';
        $this->duration = $data['duration'] ?? 0;
        $this->maxClients = $data['max_clients'] ?? 0;
        $this->location = $data['location'] ?? '';
    }

    //getters
    public function getId() { return $this->id; }
    public function getTrainerId() { return $this->trainerId; }
    public function getTitle() { return $this->title; }
    public function getDescription() { return $this->description; }
    public function getDate() { return $this->date; }
    public function getTime() { return $this->time; }
    public function getDuration() { return $this->duration; }
    public function getMaxClients() { return $this->maxClients; }
    public function getLocation() { return $this->location; }

    //setters
    public function setTitle($title) { $this->title = $title;}
    public function setDescription($description) { $this->description = $description; }
    public function setDate($date) { $this->date = $date; }
    public function setTime($time) { $this->time = $time; }
    public function setDuration($duration) { $this->duration = $duration; }
    public function setMaxClients($maxClients) { $this->maxClients = $maxClients; }
    public function setLocation($location) { $this->location = $location; }

    public function create() {
        $errors = $this->validate();
        if (!empty($errors)) {
            throw new Exception(implode(" ", $errors));
        }

        $id = OperatiiDB::create('classes', [
            'trainer_id' => $this->trainerId,
            'title' => $this->title,
            'description' => $this->description,
            'DATE' => $this->date,
            'TIME' => $this->time,
            'duration' => $this->duration,
            'max_clients' => $this->maxClients,
            'location' => $this->location
        ]);
        
        $this->id = $id;
        return $id;
    }

    public function update() {
        $errors = $this->validate();
        if (!empty($errors)) {
            throw new Exception(implode(" ", $errors));
        }

        OperatiiDB::update('classes', 
            [
                'title' => $this->title,
                'description' => $this->description,
                'DATE' => $this->date,
                'TIME' => $this->time,
                'duration' => $this->duration,
                'max_clients' => $this->maxClients,
                'location' => $this->location
            ],
            'id = :id AND trainer_id = :trainer_id',
            [':id' => $this->id, ':trainer_id' => $this->trainerId]
        );
    }

    public function delete() {
        OperatiiDB::delete('classes', 
            'id = :id AND trainer_id = :trainer_id', 
            [':id' => $this->id, ':trainer_id' => $this->trainerId]
        );
    }

    public static function findById($id, $trainerId) {
        $result = OperatiiDB::read('classes', 
            'WHERE id = :id AND trainer_id = :trainer_id', 
            [':id' => $id, ':trainer_id' => $trainerId]
        );
        
        return $result ? new FitnessClass($result[0]) : null;
    }

    public static function findByTrainer($trainerId) {
        $results = OperatiiDB::read('classes', 
            'WHERE trainer_id = :trainer_id ORDER BY date, time', 
            [':trainer_id' => $trainerId]
        );
        
        $classes = [];
        foreach ($results as $row) {
            $classes[] = new FitnessClass($row);
        }
        return $classes;
    }

    public static function findAvailable() {
        // TODO: Implementare pt clienti
    }

    public function hasConflict() {
        // TODO: Verifica daca trainerul are alta clasa la aceeasi data si ora
    }

    public function isFull() {
        // TODO: Verifica daca clasa e full
    }

    public function getEnrolledCount() {
        // TODO: Returneaza nr de clienti inscrisi la o clasa
    }

    public function enroll($clientId) {
        // TODO: Inscrie un client la aceasta clasa
    }

    public function unenroll($clientId) {
        // TODO: Sterge inscrierea unui client de la aceasta clasa
    }

    public function isEnrolled($clientId) {
        // TODO: Verifica daca un client este deja inscris
    }

    public function validate() {
        $errors = [];

        if (empty(trim($this->title))) {
            $errors[] = "Titlul este obligatoriu.";
        }

        if (empty($this->date)) {
            $errors[] = "Data este obligatorie.";
        }

        if (empty($this->time)) {
            $errors[] = "Ora este obligatorie.";
        }

        if (empty(trim($this->location))) {
            $errors[] = "Locația este obligatorie.";
        }

        if ($this->duration <= 0) {
            $errors[] = "Durata trebuie să fie pozitivă.";
        }

        if ($this->maxClients <= 0) {
            $errors[] = "Numărul maxim de clienți trebuie să fie pozitiv.";
        }

        return $errors;
    }
}
