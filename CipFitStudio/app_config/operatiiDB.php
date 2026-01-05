<?php
require_once 'connectDB.php';

class OperatiiDB {

    // SELECT cu filtrare
    public static function read($tabel, $query = "", $params = []) {
        $conn = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM $tabel $query";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // INSERT
    public static function create($tabel, $valori) {
        $conn = Database::getInstance()->getConnection();

        $coloane = implode(", ", array_keys($valori));
        $placeholders = ":" . implode(", :", array_keys($valori));

        $sql = "INSERT INTO $tabel ($coloane) VALUES ($placeholders)";

        // Debug (lasat optional)
        // echo $sql; var_dump($valori);

        $stmt = $conn->prepare($sql);
        $stmt->execute($valori);

        return $conn->lastInsertId();
    }

    // UPDATE
    public static function update($tabel, $valori, $conditie, $condParams = []) { //folosesc cond params pt prepared statement, nu pun valorile direct in conditie, ci folosesc placeholderi
        $conn = Database::getInstance()->getConnection();
        $coloane = array_keys($valori);
        for ($i = 0; $i < count($coloane); $i++) {
            $coloane[$i] = $coloane[$i] . "=:" . $coloane[$i];
        }
        $setPart = implode(", ", $coloane);

        $sql = "UPDATE $tabel SET $setPart WHERE $conditie";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge($valori, $condParams)); //execute accepta un singur array
        return $stmt->rowCount();
    }

    // DELETE
    public static function delete($tabel, $conditie, $params = []) {
        $conn = Database::getInstance()->getConnection();

        $sql = "DELETE FROM $tabel WHERE $conditie";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }
}
