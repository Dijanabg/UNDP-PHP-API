<?php
// u postman ruta je http://localhost/undp8/timovi-api/test/responseTest.php
require_once "../model/Response.php";
//provera da li ispisuje gresku
// $response = new Response();
// $response->setSuccess(false);
// $response->setHttpStatusCode(404);
// $response->addMessage("Error");
// $response->send();


//provera da li ispisuje ako je uspesno
$response = new Response();
$response->setSuccess(true); //ako posaljemo umesto true "asd" vraca error 500
$response->setHttpStatusCode(200); //ili ako posaljemo "asd" takodje vraca 500
$response->addMessage("success");
$response->send();
