<?php

class Login{
    private static $instance = null;

    private $username;
    private $password;

    private function __construct($username, $password){
        $this->username = $username;
        $this->password = $password;
    }

    public static function getInstance($username, $password){
        if (self::$instance == null) {
            self::$instance = new Login($username, $password);
        }
            return self::$instance;
    }

    public function korisnik(){
        return $this->username;
    }
}