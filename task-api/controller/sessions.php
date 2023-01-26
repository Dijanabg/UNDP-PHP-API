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
            $query = "UPDATE tblusers SET loginattempts = $db_loginattempts +1 WHERE  id='$db_id'";
            $result = $conn->query($query);

            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Username or password is incorrect"); //iako znamo da je password, zbog sigurnosti idse ovakva poruka
            $response->send();

            exit;
        }
        //uspesan login pokrenuti sesiju
        //generisemo access token
        $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))); //pretvaranje iz binarnog u heksadecimalni a zatim u base64 //standardan format u kome cuvamo tokene

        $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24))); //pretvaranje iz binarnog u heksadecimalni a zatim u base64 //standardan format u kome cuvamo tokene

        $access_expiry = 1800; //30min u sek
        $refresh_expiry = 1800000; // oko tri nedelje u sek


    } catch (Exception $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error");

        $response->send();
        exit;
    }
    $conn->begin_transaction();
    try {
        //vracamo loginattempts na 0 kada se uloguje user
        $query = "UPDATE tblusers SET loginattempts = 0 WHERE id=$db_id";
        $conn->query($query);

        //kreiramo sessiju
        $query = "INSERT INTO tblsessions (userid, accesstoken, accessexpiry, refreshtoken, refreshexpiry) VALUES ($db_id, '$accesstoken', DATE_ADD(now(), INTERVAL $access_expiry SECOND), '$refreshtoken', DATE_ADD(now(), INTERVAL $refresh_expiry SECOND))";
        $conn->query($query);


        $last_id = $conn->insert_id; //pronalazenje id sesije koja je poslednja dodata
        $conn->commit();

        $returnData = array();
        $returnData['session_id'] = intval($last_id);
        $returnData['accesstoken'] = $accesstoken;
        $returnData['refreshtoken'] = $refreshtoken;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage("User logged in, access yoken created");
        $response->setData($returnData);
        $response->send();

        exit;
    } catch (Exception $ex) {
        //ponistavamo promene nad bazom
        $conn->rollback();
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("Error logging user");

        $response->send();
        exit;
    }
}
