<?php
require_once("{$_SERVER['DOCUMENT_ROOT']}../../config/db_config.php");

class Base
{
    protected $db_config;
    public $pdo;

    public function __construct($db_config) {
        $this->db_config = $db_config;
        $this->pdo = new PDO( "mysql:host={$this->db_config["host"]};dbname={$this->db_config["dbname"]}", $this->db_config["user"], $this->db_config["password"]);
    }
}