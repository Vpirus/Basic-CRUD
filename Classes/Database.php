<?php

    Class Database {
        private $host = "localhost";
        private $username ="root";
        private $password = "";
        private $database = "registration_db";
        public $connection;

        public function connect() {
            $this->connection = new mysqli($this->host,$this->username,$this->password,$this->database);
            if($this->connection->connect_error){
                die("Connection Error". $this->connection->connect_error);
            } else {
                return $this->connection;
            }
        }
    }
?>