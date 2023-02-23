<?php
include "UserFactory.php";
$factory = new UserFactory;
$user1 = $factory->kreiraj('Ana', 'Anic');
$user2 = $factory->kreiraj('Milica', 'Milic');

print_r($user1->ispisiProfil());
print_r($user2->ispisiProfil());