<?php

class User{
    private $ime;
    private $prezime;

    public function __construct($ime, $prezime){
        $this->ime=$ime;
        $this->prezime=$prezime;
    }

    public function ispisiProfil(){
        return "Ime usera je: ". $this->ime.'; Prezime usera: '.$this->prezime;
    }
}