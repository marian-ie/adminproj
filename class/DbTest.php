<?php
    class DbTest {
        private $conn;

        public function __construct($db) {
            $this->conn = $db;
        }

        public function checkconnection() {
            if($this->conn) {
                return ["status" => "success", "message" => "Database connected successfully"];
            } else {
                return ["status" =>  "error", "message" => "Failed to connect to database"];
            }
        }
    }
?>