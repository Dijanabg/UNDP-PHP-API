<?php


require('db.php');
require('../model/Response.php');
//  nikada ne kesirati klijentske http zahteve/odgovore!
// (u nasem response modelu po difoltu nema kesiranja osim ako se specificno ne navede)


$conn = DB::connectDB();

// registracija korisnika
// obrada kreiranja novog user-a
// porovera da li je iskljucivo POST zahtev - inace izlaz sa error odgovorom

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = new Response();
    $response->addMessage('Only POST method is allowed');
    $response->send();
    exit;
}
if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Content Type header not set to JSON");
    $response->send();
    exit;
  }  
// prihvatanje tela PATCH zahteva kao da ce PATCHed data biti JSON format
$rawPostData = file_get_contents('php://input');

$jsonData = json_decode($rawPostData);


// provera da li post zahtev sadrzi puno ime, username i password u telu
if (!isset($jsonData->fullname) || !isset($jsonData->username) || !isset($jsonData->password)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage('Missing fullname, username or password');
    $response->send();

    exit;
}

if (strlen(trim($jsonData->fullname)) < 1 || strlen($jsonData->username) < 1 || strlen($jsonData->password) < 5) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage('Fullname, username or password not long enough');
    $response->send();
    exit;
}


$fullname = trim($jsonData->fullname); // brisemo prazna polja (prazne karaktere)
$username = trim($jsonData->username);
$password = $jsonData->password;

try {
    $query = "SELECT id FROM tblusers WHERE username='$username'";
    $result = $conn->query($query);
    $rowCount = mysqli_num_rows($result);

    if ($rowCount !== 0) {
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage('Username already exists');
        $response->send();
        exit;
    }
// hesing sifre da bi se ubacila u bazu, jer cuvanje sifre kao obicnog teksta nije dobra praksa
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);

//dodavanje korisnika
    $query = "INSERT INTO tblusers (fullname, username, password) VALUES ('$fullname', '$username', '$hashed_pass')";
    $inserted = $conn->query($query);

    if ($inserted === false) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage('Error creating user');
        $response->send();

        exit;
    }

    $returnData = array();
    $returnData['fullname'] = $fullname;
    $returnData['username'] = $username;

    $response = new Response();
    $response->setHttpStatusCode(201);
    $response->setSuccess(true);
    $response->addMessage('User created');
    $response->setData($returnData);
    $response->send();

    exit;
} catch (Exception $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage('There was an issue creatiung user');
    $response->send();

    exit;
}
