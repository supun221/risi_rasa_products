<?php 

class Database {
    private $server;
    private $username;
    private $password;
    private $db;
    private $connection;

    public function __construct($server, $username, $password, $db) {
        $this->server = $server;
        $this->username = $username;
        $this->password = $password;
        $this->db = $db;
    }

    public function connect(){
        $this->connection = new mysqli($this->server, $this->username, $this->password, $this->db);

        if($this->connection->connect_error){
           die("Error occured! " . $this->connection->connect_error);
        }

        return $this->connection;
    }
}

$db_conn = (new Database('localhost', 'root', '', 'nexaraso_grinding_meals'))->connect();

?>