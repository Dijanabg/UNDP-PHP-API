<?php
include "User.php";
class UserFactory{

    public function kreiraj($ime, $prezime){
        return new User($ime, $prezime);
    }
}