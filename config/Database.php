<?php
namespace App;

use PDO;
use PDOException;

class Database
{
    private $pdo;

    public function __construct(
        $host = 'localhost',
        $dbname = 'phichaia_app',
        $username = 'root',
        $password = ''
        // $username = 'phichaia_stdcare',
        // $password = '48dv_m64N'
    ) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }    // เพิ่มเมธอดนี้
    public function query($sql, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new \Exception('Database query error: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->pdo;
    }


}
