<?php
namespace Barkios\core;
use PDO;
use PDOException;

abstract class Database extends PDO{

    protected $db;

    public function __construct() {
        try {
            $this->db = new PDO(
                'mysql:host=localhost;dbname=barkios_db;charset=utf8',
                'root',
                '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        } catch (PDOException $e) {

            die("Error de conexión: " . $e->getMessage());
        }
    }
}