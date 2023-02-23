<?php

require "Login.php";

$username = "stasha";
$password = "stasha123";


$instanca1 = Login::getInstance($username, $password);
$korisnik1 = $instanca1->korisnik();
print_r($korisnik1);

$instanca2 = Login::getInstance($username, $password);
$korisnik2 = $instanca2->korisnik();
print_r($korisnik2);


if ($instanca1 === $instanca2){
    echo "U pitanju je jedna instanca. <br>";
} else {
    echo "Kreirano je vise instanci. <br>";
}

if ($korisnik1 === $korisnik2){
    echo "U pitanju je isti korisnik. <br>";
} else {
    echo "Kreirano je vise korisnika. <br>";
}