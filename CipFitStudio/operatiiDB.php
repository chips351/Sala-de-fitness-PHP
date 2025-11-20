<?php
require_once 'connectDB.php';

class OperatiiDB {

    // SELECT cu filtrare
    public static function read($tabel, $query = "", $params = []) {
        $conn = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM $tabel $query";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // <- IMPORTANT!
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
    public static function update($tabel, $valori, $conditie, $condParams = []) {
        $conn = Database::getInstance()->getConnection();

        $setPart = [];
        foreach ($valori as $coloana => $valoare) {
            $setPart[] = "$coloana = :set_$coloana";
        }
        $setPart = implode(", ", $setPart);

        // Convertim :set_nume în array
        $updateParams = [];
        foreach ($valori as $coloana => $valoare) {
            $updateParams["set_$coloana"] = $valoare;
        }

        $sql = "UPDATE $tabel SET $setPart WHERE $conditie";

        $stmt = $conn->prepare($sql);
        $stmt->execute(array_merge($updateParams, $condParams));
    }

    // DELETE
    public static function delete($tabel, $conditie, $params = []) {
        $conn = Database::getInstance()->getConnection();

        $sql = "DELETE FROM $tabel WHERE $conditie";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
    }
}