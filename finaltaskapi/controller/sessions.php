<?php

require('db.php');
require('../model/Response.php');
// nikada ne kesirati login ili token http zahteve/odgovore
// (u nasem response modelu po difoltu nema kesiranja osim ako se specificno ne navede)

$conn = DB::connectDB();

// ruta 1: /sessions/3  DELETE
// ruta 2: /sessions/4  PATCH
// ruta 3: /sessions    POST
// provera da li je sessionid u url-u npr. /sessions/1 
if (isset($_GET['sessionid'])) {
    // implementiramo DELETE i PATCH
    $sessionid = $_GET['sessionid'];
    if($sessionid == '' || !is_numeric($sessionid)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        ($sessionid == '' ? $response->addMessage("Session ID cannot be blank") : false);
        (!is_numeric($sessionid) ? $response->addMessage("Session ID must be numeric") : false);
        $response->send();
        exit;
      }
      
// provera da li je access token prosledjen u http authentification header-u i da je vrednost veca od 0 karaktera
// ne zaboraviti Apache fix u .htaccess fajlu?????
// 401 error je za neuspelu autentifikaciju ili ako nije jos uvek obezbedjena
      if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1)
      {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Access token is missing from the header") : false);
        (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Access token cannot be blank") : false);
        $response->send();
        exit;
      }
      
// prihvatanje supplied access token-a iz authorisation header-a - za delete (log out) i patch (refresh)
      $accesstoken = $_SERVER['HTTP_AUTHORIZATION'];
      
// ako je zahtev DELETE, npr. delete session
      if($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        //izvrsiti upit nad bazom za proveru detalja tokena
        try {
// kreirati upit za brisanje sesije gde je access token jednak prosledjenom (ostaviti druge sesije aktivne)
// nije bitno da li je access token istekao jer brisemo sesiju
          $query = "DELETE FROM tblsessions where id = $sessionid";
          $result = $conn->query($query);
    
          $rowCount = $result->num_rows;///**********neka greska num_rows <b>Warning</b>:  Attempt to read property "num_rows" on bool in <b>C:\xampp\htdocs\undp8\finaltaskapi\controller\sessions.php</b> on line <b>53</b><br />
          if($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Failed to log out of this session using access 	token provided");
            $response->send();
            exit;
          }
// napraviti response niz sa podacima koji sadrzi id sesije koja je obrisana(izlogovana)
    $returnData = array();
    $returnData['id'] = intval($sessionid);

    $response = new Response();
    $response->setHttpStatusCode(200);
    $response->setSuccess(true);
    $response->setData($returnData);
    $response->send();
    exit;
  }
  catch(mysqli_sql_exception $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("There was an issue logging out - please try again");
    $response->send();
    exit;
  }
}
// ako je zahtev PATCH, npr. renew access token 
elseif($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    
    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Content Type header not set to JSON");
      $response->send();
      exit;
    }
 
    $rawPatchdata = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPatchdata)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body is not valid JSON");
      $response->send();
      exit;
    }

// provera da li patch zahtev sadrzi access token
    if(!isset($jsonData->refreshtoken) || strlen($jsonData->refreshtoken) < 1)  {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (!isset($jsonData->refreshtoken) ? $response->addMessage("Refresh Token not supplied") : false);
      (strlen($jsonData->refreshtoken) < 1 ? $response->addMessage("Refresh Token cannot be blank") : false);
      $response->send();
      exit;
    }
    try{
      
        $refreshtoken = $jsonData->refreshtoken;
  
// prihvatanje usera za prosledjeni id sesije, access i refresh token
// kreirati upit za prihvatanje user podataka iz prosledjenog access i refresh token-a 
        //$query = "SELECT tblsessions.id as sessionid, tblsessions.userid as userid, accesstoken, refreshtoken, useractive, loginattempts, accessexpiry, refreshexpiry from tblsessions, tblusers where tblusers.id = tblsessions.userid and tblsessions.id = sessionid and tblsessions.accesstoken = tblusers.accesstoken and tblsessions.refreshtoken = tblusers.refreshtoken';
        $query="SELECT tblsessions.id as sessionid, tblsessions.userid as userid, accesstoken, accessexpiry, refreshtoken, refreshexpiry, loginattempts FROM tblusers, tblsessions WHERE tblsessions.userid = tblusers.id AND sessionid = $sessionid AND tblsessions.accesstoken = $accesstoken and tblsessions.refreshtoken = $refreshtoken";
        $result = $conn->query($query);
  
        $rowCount = $result->num_rows;
  
        if($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(401);
          $response->setSuccess(false);
          $response->addMessage("Access Token or Refresh Token is incorrect session id");
          $response->send();
          exit;
        }
        
        $row = $result->fetch_assoc();
  
// sacuvati povratne vrednosti
        $returned_sessionid = $row['sessionid'];
        $returned_userid = $row['userid'];
        $returned_accesstoken = $row['accesstoken'];
        $returned_refreshtoken = $row['refreshtoken'];
        $returned_useractive = $row['useractive'];
        $returned_loginattempts = $row['loginattempts'];
        $returned_accessexpiry = $row['accessexpiry'];
        $returned_refreshexpiry = $row['refreshexpiry'];
// provera da li je nalog aktivan
        if($returned_useractive != 'Y') {
          $response = new Response();
          $response->setHttpStatusCode(401);
          $response->setSuccess(false);
          $response->addMessage("User account is not active");
          $response->send();
          exit;
        }
  
// provera da li je nalog zakljucan
        if($returned_loginattempts >= 3) {
          $response = new Response();
          $response->setHttpStatusCode(401);
          $response->setSuccess(false);
          $response->addMessage("User account is currently locked out");
          $response->send();
          exit;
        }
  
// provera da li je refresh token istekao
        if(strtotime($returned_refreshexpiry) < time()) {
          $response = new Response();
          $response->setHttpStatusCode(401);
          $response->setSuccess(false);
          $response->addMessage("Refresh token has expired - please log in again");
          $response->send();
          exit;
        }
        // generisanje access tokena
        // uzeti 24 random bajta da se generise token i onda sifrovati kao base64
        // staviti unix time stamp kao sufiks za garanciju jedinstvenosti (stale 	tokens)
        $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
  
        // generisanje refresh tokena
        // uzeti 24 random bajta da se generise token i onda sifrovati kao base64
        // staviti unix time stamp kao sufiks za garanciju jedinstvenosti (stale 	tokens)
        $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
  
        // setovati access token and refresh token rok u sekundama (access token 20 minuta vek i refresh token 14 dana)
        // bolje je slati sekunde nego date/time jer vremenske zone ne uticu na to
        $accessexpiry_seconds = 1200;
        $refreshexpiry_seconds = 1209600;
        
        //$query = "UPDATE tblsessions SET accesstoken = '$accesstoken', accessexpiry = DATE_ADD(NOW(), INTERVAL $accessexpiry_seconds SECOND), refreshtoken = $refreshtoken, refreshexpiry = date_add(NOW(), INTERVAL $refreshexpiry_seconds SECOND) where id = $sessionid and userid = $db_userid and accesstoken = $returnedaccesstoken and refreshtoken = $returnedrefreshtoken');
        // azurirati broj redova - trebalo bi da bude 1
        $query = "UPDATE tblsessions SET accesstoken = $accesstoken, accessexpiry = DATE_ADD(NOW(), INTERVAL $accessexpiry_seconds SECOND, refreshtoken = $refreshtoken, refreshexpiry = date_add(NOW(), INTERVAL $refreshexpiry_seconds SECOND) where id = $sessionid and accesstoken = $returned_accesstoken and refreshtoken = $returned_refreshtoken)";
        $result = $conn->query($query);
        $rowCount = mysqli_num_rows($result);
        //$rowCount = $query->rowCount();
        
        if($rowCount === 0) {
          $response = new Response();
          $response->setHttpStatusCode(401);
          $response->setSuccess(false);
          $response->addMessage("Access token could not be refreshed - please log in again");
          $response->send();
          exit;
        }
  
        $returnData = array();
        $returnData['session_id'] = $returned_sessionid;
        $returnData['accesstoken'] = $accesstoken;
        $returnData['accessexpiry'] = $accessexpiry_seconds;
        $returnData['refreshtoken'] = $refreshtoken;
        $returnData['refreshexpiry'] = $refreshexpiry_seconds;
  
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit;
      }
      catch(mysqli_sql_exception $ex) {
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage("There was an issue refreshing access token - please 	log in again");
        $response->send();
        exit;
      }
      
    }
    // error za slucaj da nije DELETE ili PATCH
    else {
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("Request method not allowed");
      $response->send();
      exit;
    } 
  }
  
 else {
    // implementiramo POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response = new Response();
        $response->addMessage('Only POST method is allowed');
        $response->send();
        exit;
    }

    $rawPostData = file_get_contents('php://input');
    $jsonData = json_decode($rawPostData);

    // provera podataka
    if (!isset($jsonData->username) || !isset($jsonData->password)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage('Missing username or password');
        $response->send();

        exit;
    }

    if (strlen($jsonData->username) < 1 || strlen($jsonData->password) < 5) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage('Username or password not long enough');
        $response->send();

        exit;
    }

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
            $response->addMessage('Username or password is incorrect'); // dobra praksa
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
            //implmentirati login attempts

            $query = "UPDATE tblusers SET loginattempts = $db_loginattempts + 1 WHERE id = $db_id";
            $result = $conn->query($query);

            $response = new Response();
            $response->setHttpStatusCode(409);
            $response->setSuccess(false);
            $response->addMessage('Username or password is incorrect'); // dobra praksa
            $response->send();

            exit;
        }
        //uspesan login
        //treba da pokrenemo sesiju

        //generisemo access token
        $accesstoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)));
        $refreshtoken = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)));

        $access_expiry = 1800; // 30 minuta
        $refresh_expiry = 1800000; //priblizno 3 nedelje

    } catch (Exception $ex) {
        //implementirati
    }

    $conn->begin_transaction();
    try {
        //resetujemo loginattempts na vrednost 0
        $query1 = "UPDATE tblusers SET loginattempts = 0 WHERE id = $db_id";
        $conn->query($query1);

        //kreiramo sesiju
        $query2 = "INSERT INTO tblsessions (userid, accesstoken, accessexpiry, refreshtoken, refreshexpiry) 
                VALUES ($db_id, '$accesstoken', DATE_ADD(now(), INTERVAL $access_expiry SECOND), '$refreshtoken', DATE_ADD(now(), INTERVAL $refresh_expiry SECOND))";
        $conn->query($query2);

        // brz nacin da pronadjemo id sesije koja je poslednja dodata
        $last_id = $conn->insert_id;

        $conn->commit();

        $returnData = array();
        $returnData['session_id'] = intval($last_id);
        $returnData['accesstoken'] = $accesstoken;
        $returnData['refreshtoken'] = $refreshtoken;

        $response = new Response();
        $response->setHttpStatusCode(201);
        $response->setSuccess(true);
        $response->addMessage('User logged in, access token created');
        $response->setData($returnData);
        $response->send();

        exit;
    } catch (Exception $ex) {
        // ponistavamo promene nad bazom
        $conn->rollback();

        //response
        $response = new Response();
        $response->setHttpStatusCode(500);
        $response->setSuccess(false);
        $response->addMessage('Error logging user');
        $response->send();
    }
}
