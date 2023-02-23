<?php
include "AutomobilFactory.php";
$factory = new AutomobilFactory;
$vozilo1 = $factory->kreiraj('BMW', 'X5');
$vozilo2 = $factory->kreiraj('Reno', 'Megan');

print_r($vozilo1->ispisiMarku());
print_r($vozilo2->ispisiMarku());