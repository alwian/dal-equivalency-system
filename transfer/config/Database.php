<?php
class Database
{
    // Database connection details.
    private $dbname = 'lab6';
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $conn;

    /**
     * Connect to the database.
     * @return PDO|null A connection, null when error occurs.
     */
    public function connect() {
        $this->conn = null;

        try {
            $this->conn = new PDO('mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=UTF8;', $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo $e->getMessage();
        }
        return $this->conn;
    }
}