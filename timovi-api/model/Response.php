<?php

class Response
{
    private $_success;
    private $_httpStatusCode;
    private $_messages = array();
    private $_data;
    private $_toCache = false;
    private $_responseData; //ceo objekat vraca

    public function setSuccess($success)
    {
        $this->_success = $success;
    }

    public function setHttpStatusCode($httpStatusCode)
    {
        $this->_httpStatusCode = $httpStatusCode;
    }

    public function addMessage($messages)
    {
        $this->_messages[] = $messages;
    }

    public function setData($data)
    {
        $this->_data = $data;
    }

    public function ToCache($toCache)
    {
        $this->_toCache = $toCache;
    }
    public function send() //uzima sve iznad i salje kao odgovor
    {   //****ovo se salje kroz header
        header('Content-type: application/json; charset=utf-8'); //oznacava da je tip podataka koji se vraca json objekat i da moze da ima i cirilicu, smajlije itd

        //ako kesiramo podatke
        if ($this->_toCache == true) {
            header('Cache-control: max-age = 120'); //kesirani podaci se cuvaju 120s
        } else {
            header('Cache-control: no-cache, no-store');
        }
        //***** ovo se salje kroz body
        //ispitujemo success status 
        if (($this->_success !== false && $this->_success !== true) || !is_numeric($this->_httpStatusCode)) { //greska do servera jer mi podesavamo vracanje statusa

            http_response_code(500); //kroz header
            $this->_responseData['statucCode'] = 500;
            $this->_responseData['success'] = false;
            $this->addMessage("Response creation error"); //setujemo poruke(poruka nastala pri samom kreiranju)
            $this->_responseData['messages'] = $this->_messages;
        } else { //ima iste podatke kao if iznad samo da su pozitivni

            http_response_code($this->_httpStatusCode); //kroz header ako nije 500 saljemo neku nasu gresku npr. 200,302..
            $this->_responseData['statucCode'] = $this->_httpStatusCode; //setuju podatke
            $this->_responseData['success'] = $this->_success;
            $this->_responseData['messages'] = $this->_messages;
            $this->_responseData['data'] = $this->_data;
        }
        echo json_encode($this->_responseData); //vraca objekat json format *********kroz body
    }
}
