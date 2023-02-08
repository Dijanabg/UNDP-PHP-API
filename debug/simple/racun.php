<?php

class Racun
{
private $iznos;
private $tip;

public function __contruct($iznos, $tip)
{
$this->iznos = $iznos;
$this->tip = $tip;
}

function set_iznos($iznos)
{
$this->iznos = $iznos;
}
function get_iznos()
{
return $this->iznos;
}
}

$racun1 = new Racun();
$racun2 = new Racun();
$racun1->set_iznos('300');

$racun2->set_iznos(250);

echo $racun1->get_iznos();
echo "<br>";
echo $racun2->get_iznos();

?>