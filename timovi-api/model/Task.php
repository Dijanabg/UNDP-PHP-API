<?php

class Task
{
    private $_timID;
    private $_nazivTima;
    private $_drzava;
    private $_brojTitula;
    private $_godinaOsnivanja;

    public function __construct($timID, $nazivTima, $drzava, $brojTitula, $godinaOsnivanja)
    {
        $this->_timID = $timID;
        $this->_nazivTima = $nazivTima;
        $this->_drzava = $drzava;
        $this->_brojTitula = $brojTitula;
        $this->_godinaOsnivanja = $godinaOsnivanja;
    }

    public function getTimID()
    {
        return $this->_timID;
    }


    public function setTimID($timID)
    {
        $this->_timID = $timID;
    }

    public function getNazivTima()
    {
        return $this->_nazivTima;
    }

    public function setNazivTima($nazivTima)
    {
        $this->_nazivTima = $nazivTima;
    }

    public function getDrzava()
    {
        return $this->_drzava;
    }

    public function setDrzava($drzava)
    {
        $this->_drzava = $drzava;
    }

    public function getBrojTitula()
    {
        return $this->_brojTitula;
    }

    public function setBrojTitula($brojTitula)
    {
        $this->_brojTitula = $brojTitula;
    }

    public function getGodinaOsnivanja()
    {
        return $this->_godinaOsnivanja;
    }

    public function setGodinaOsnivanja($godinaOsnivanja)
    {
        $this->_godinaOsnivanja = $godinaOsnivanja;
    }

    //nakon kreiranih setera i getera pravimo metodu koja vraca task kao niz
    public function returnTaskArray()
    {
        $task = array();
        $task['timID'] = $this->getTimID();
        $task['nazivTima'] = $this->getNazivTima();
        $task['drzava'] = $this->getDrzava();
        $task['brojTitula'] = $this->getBrojTitula();
        $task['godinaOsnivanja'] = $this->getGodinaOsnivanja();
        return $task;
    }
}
