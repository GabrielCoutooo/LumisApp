<?php
// config/database.php

class Database
{
    private $host = 'localhost';
    private $db_name = 'lumis'; // ajuste conforme o nome do banco no banco.sql
    private $username = 'root'; // ajuste conforme seu ambiente
    private $password = '';
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
