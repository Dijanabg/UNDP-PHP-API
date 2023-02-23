<?php 
class ConnectDb {

  private static $instance = null;
  private $conn;
  
  private $host = 'localhost';
  private $user = 'root';
  private $pass = '';
  private $db = 'dbtasks';
   
 
  private function __construct()
  {
    $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->db);
  }
  
  public static function getInstance()
  {
    if(!self::$instance)
    {
      self::$instance = new ConnectDb();
    }
   
    return self::$instance;
  }
  
  public function getConnection()
  {
    return $this->conn;
  }
}
