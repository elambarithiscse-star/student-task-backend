<?php
/**
 * Database Configuration (Railway Version with SSL)
 */

class Database
{
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $host = getenv("MYSQLHOST");
            $db_name = getenv("MYSQLDATABASE");
            $username = getenv("MYSQLUSER");
            $password = getenv("MYSQLPASSWORD");
            $port = getenv("MYSQLPORT");

            $this->conn = new PDO(
                "mysql:host={$host};port={$port};dbname={$db_name};sslmode=require",
                $username,
                $password,
                [
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            );

            $this->conn->exec("set names utf8");

        } catch (PDOException $exception) {
            echo json_encode([
                "success" => false,
                "message" => "Database connection error"
            ]);
            exit;
        }

        return $this->conn;
    }
}
?>
