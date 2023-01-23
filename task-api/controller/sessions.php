<?php

require("db.php");
require("../model/Response.php");

$conn = DB::connectDB();

//ruta 1: /sessions/3 DELETE
//ruta 2: /sessions/4 PATCH
//ruta 3: /sessions POST

if (isset($_GET['sessionid'])) {
    //implementiramo delete i patch
} else {
    //implementriramo post
    if ($_SERVER['REQUEST_METHOD'] !== "POST") {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Only POST method is allowed");
        $response->send();
        exit;
    }
    $rawPostData = file_get_contents('php://input');
    $jsonData = json_decode($rawPostData);

    //provera podataka LOGOVANJA
    if (!isset($jsonData->username) || !isset($jsonData->password)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        //$response->addMessage("Missing fullname, username or password");

        if (!isset($jsonData->username))
            $response->addMessage('Username filed is mandatory and must be provided');
        if (!isset($jsonData->password))
            $response->addMessage('Password filed is mandatory and must be provided');
        $response->send();
        exit;
    }
    //proveravamo duzinu unetih polja
    if (strlen($jsonData->username) < 1 || strlen($jsonData->password) < 5) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Username or password is not long enough");

        $response->send();
        exit;
    }
    //sklanja prazna polja sa pocetka i kraja stringa trim
    $username = trim($jsonData->username);
    $password = $jsonData->password;

    try {
        $query = "SELECT id, fullname, username, password, useractive, loginattempts FROM tblusers WHERE username='$username'";
        $result = $conn->query($query);
        $rowCount = mysqli_num_rows($result);
        if ($rowCount === 0) {

            $response = new Response();
            $response->setHttpStatusCode(409);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect"); //iako znamo da je username, zbog sigurnosti idse ovakva poruka
            $response->send();

            exit;
        }
        $row = $result->fetch_assoc();
        $db_id = $row['id'];
        $db_fullname = $row['fullname'];
        $db_username = $row['username'];
        $db_password = $row['password'];
        $db_useractive = $row['useractive'];
        $db_loginattempts = $row['loginattempts'];

        if (!password_verify($password, $db_password)) {
            //implementirati loginattempts
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect"); //iako znamo da je password, zbog sigurnosti idse ovakva poruka
            $response->send();

            exit;
        }
        //uspesan login pokrenuti sesiju

    } catch (Exception $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error");

        $response->send();
        exit;
    }
}
