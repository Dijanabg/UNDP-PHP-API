<?php
$tekst= "debugging<br>";
echo $tekst;


for($i=0; $i<5; $i++){
    echo "Broj ".$i."<br>";
}

class Macka{
    private $ime; 

    /**
     * Get the value of ime
     */ 
    public function getIme()
    {
        return $this->ime;
    }

    /**
     * Set the value of ime
     *
     * @return  self
     */ 
    public function setIme($ime)
    {
        $this->ime = $ime;

        return $this;
    }
}
$macka1 = new Macka();

$macka1->setIme("Cicka");
$macka2 = new Macka();
$macka2->setIme("Ozi");
echo $macka1->getIme();
echo $macka2->getIme();