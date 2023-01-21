<?php

require "db.php";
require_once "../model/Response.php";
require_once "../model/Task.php";

$conn = DB::connectDB();

//tasks GET **vraca sve taskove
//tasks POST ** kreira task
//tasks/1 PUT/PATCH **izmeni tasks sa id=1
//tasks/1 GET **vraca tasks sa id=1
//tasks/1 DELETE **brise tasks sa id=1


if (isset($_GET['timID'])) {
    $taskid = $_GET['timID'];

    //prva greska da li je id broj i da li je poslato prazno

    if (!is_numeric($taskid) || $taskid == '') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage('Task ID cannot be blank or must be numeric');
        $response->send();
        exit;
    }
    //zatim proveravamo da li poslat zahtev zaista GET u else grani
    // nakon toga u if grani pozivamo iz baze query i broj vracenih redova
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $query = "SELECT * FROM tim where timID=$taskid";
        $result = $conn->query($query);

        $rowCount = $result->num_rows;
        //sta ako je broj redova koji je vracen jednak nuli
        if ($rowCount == 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('Task not found');
            $response->send();
            exit;
        }

        //while ($row = $result->fetch_assoc()) {
        $row = $result->fetch_assoc();
        $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);
        $taskArray[] = $task->returnTaskArray();
        //}
        //kreiramo niz koji cemo vratiti korisniku
        $returnData = array();
        $returnData['row_retured'] = $rowCount; //koliko je redova vraceno
        $returnData['tim'] = $taskArray; //vrati mi podatke (objekat)
        $response = new Response(); //vraca response 
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData); //vraca podatke odnosno niz returnData
        $response->send();
        exit;
    }
    //ovde pisemo delete jer je u glavnom ifu gde je get=taskid
    elseif ($_SERVER['REQUEST_METHOD'] === "DELETE") {
        $query = "DELETE FROM tim WHERE timId=$taskid";
        $result = $conn->query($query);
        //proveravamo moguce greske, recimo da u tabeli nema tog id
        $num_rows = $conn->affected_rows;
        if ($num_rows === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('Taks not found.');
            $response->send();
            exit;
        }
        //kreiramo response za pozitivan
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage('Taks delete');
        $response->send();
        exit;
    }
    //ovde pisemo kod za patch
    elseif ($_SERVER['REQUEST_METHOD'] === "PATCH") {

        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Content type header not set to JSON');
            $response->send();
            exit;
        }
        //treba da nadjemo id elementa koji zelimo da izmenimo
        $rowPatchData = file_get_contents('php://input'); //kupimo podatke iz input polja
        if (!$jsonData = json_decode($rowPatchData)) { //probamo da dekodiramo podatke u json, provera validnosti
            //ako podaci nisu dobri ili su null treba da baci error
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Request body not valid json');
            $response->send();
            exit;
        }
        //greska ukoliko ne postoji taj id element
        $query = "SELECT * FROM tim WHERE timID= $taskid";
        $result = $conn->query($query);

        $rowCount = $result->num_rows;
        //sta ako je broj redova koji je vracen jednak nuli
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('No task found to update');
            $response->send();
            exit;
        }
        //ako je pozitivno stavljamo da po difoltu nijedan nije za azuriranje
        $nazivTima_update = false;
        $drzava_update = false;
        $brojTitula_update = false;
        $godinaOsnivanja_update = false;
        //polja za azuriranje
        $queryFields = "";
        //dekodiranjem smo pretvorili u json objekat
        //podaci koje pratimo da li su azurirani
        if (isset($jsonData->nazivTima)) {
            $nazivTima_update = true;
            $queryFields .= "nazivTima='$jsonData->nazivTima',";
        }
        if (isset($jsonData->drzava)) {
            $drzava_update = true;
            $queryFields .= "drzava='$jsonData->drzava',";
        }
        if (isset($jsonData->brojTitula)) {
            $brojTitula_update = true;
            $queryFields .= "brojTitula='$jsonData->brojTitula',";
        }
        if (isset($jsonData->godinaOsnivanja)) {
            $godinaOsnivanja_update = true;
            $queryFields .= "godinaOsnivanja='$jsonData->godinaOsnivanja',";
        }
        //ako nista nije setovano za azuriranje a da ne bi izbrisao objekat
        if ($nazivTima === false && $drzava === false && $brojTitula === false && $godinaOsnivanja === false) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("No task fields provided");
            $response->send();
            exit;
        }
        //hvatamo podatke za ispis iz baze
        $queryFields = rtrim($queryFields, ","); //uklanja poslednji zarez u nizu $queryFields
        $queryString = "UPDATE tim SET $queryFields WHERE timID=$taskid";
        $result2 = $conn->query($queryString);
        //sta ako nije lepo azuriran red u tabeli

        $row = $result->fetch_assoc();

        $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);

        $queryFieldsCheck = "";
        if ($nazivTima_update) {
            $task->setNazivTima($jsonData->nazivTima);
            $queryFieldsCheck .= "nazivTima='{$task->getNazivTima()}' AND ";
        }
        if ($drzava_update) {
            $task->setDrzava($jsonData->drzava);
            $queryFieldsCheck .= "drzava='{$task->getDrzava()}' AND ";
        }
        if ($brojTitula_update) {
            $task->setBrojTitula($jsonData->brojTitula);
            $queryFieldsCheck .= "brojTitula='{$task->getBrojTitula()}' AND ";
        }
        if ($godinaOsnivanja_update) {
            $task->setGodinaOsnivanja($jsonData->godinaOsnivanja);
            $queryFieldsCheck .= "godinaOsnivanja='{$task->getGodinaOsnivanja()}' AND ";
        }
        $queryFieldsCheck .= "timID='{$task->getTimID()}'";

        $query3 = "SELECT * FROM tim WHERE $queryFieldsCheck";
        $result3 = $conn->query($query3);

        $rowCount = $result3->num_rows;
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Task not updated - given values must be the same as the stored values");
            $response->send();
            exit;
        }
        //SELECT * FROM tasks WHERE nazivTima='...' AND brojTitula='...' AND ...
        //potrebno je napisati upit koji proverava da li postoji element sa propertijima iz tabele
        //ako nema vracenih redova
        $result4 = $conn->query("SELECT * FROM tim WHERE timID=$taskid");
        $rowCount = $result4->num_rows;
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No task found");
            $response->send();
            exit;
        }
        //ako je sve u redu
        $row = $result4->fetch_assoc(); //uzimamo podatke i smestamo u novi task ispod
        $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);

        $taskArray[] = $task->returnTaskArray();
        $returnData = array();
        $returnData['row_returned'] = $rowCount;
        $returnData['task'] = $taskArray;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage('Task updated');
        $response->setData($returnData);
        $response->send();
        exit;
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage('Request method not allowed');
        $response->send();
        exit;
    }
} elseif (isset($_GET['page'])) {
    //kod
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $page = $_GET['page'];

        if ($page == '' || !is_numeric($page)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Page number connot be blank and must be numeric!");
            $response->send();
            exit;
        }

        $limitPerPage = 10;
        $query = "SELECT count(timID) as totalNoOfTasks FROM tim";
        $result = $conn->query($query);

        $row = mysqli_fetch_assoc($result);
        $tasksCount = intval($row['totalNoOfTasks']); //ukupan broj taskova u tabeli
        // $tasksCount = intval($row['count(id)']);
        $numOfPages = ceil($tasksCount / $limitPerPage); //zaokruzivanje na veci ceo broj
        //ako imamo 21 task u tabeli
        // 1  2  2,1 - NE
        // 1  2  3 - DA
        // 10 10 1 - da bi bilo ovako

        if ($numOfPages == 0) {
            $numOfPages = 1;
        }
        // tasks/page/100
        if ($page > $numOfPages) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('Page not found');
            $response->send();
            exit;
        }
        //primer da imamo samo 21 task
        //1 - 1-10
        //2 - 11-20
        //3 - 21

        //10*(2-1) = 10
        //10*(3-1) = 20
        $offset = ($page == 1 ? 0 : $limitPerPage * ($page - 1));
        $query = "SELECT * FROM tim limit $limitPerPage offset $offset";
        $result2 = $conn->query($query);

        $rowCount = $result2->num_rows;
        while ($row = $result2->fetch_assoc()) {
            $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);
            $taskArray[] = $task->returnTaskArray();
        }

        $returnData = array();
        $returnData['row_returned'] = $rowCount;
        $returnData['total_rows'] = $tasksCount;
        $returnData['total_pages'] = $numOfPages;
        $returnData['has_next_page'] = ($page < $numOfPages) ? true : false;
        $returnData['has_previous_page'] = ($page > 1) ? true : false;
        $returnData['tasks'] = $taskArray;

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit;
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}

//ovde pisemo GET all da ispise sve taskove, zato sto nam ne treba id
elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $query = "SELECT * FROM tim";
        $result = $conn->query($query);

        $rowCount = $result->num_rows;
        //sta ako je broj redova koji je vracen jednak nuli
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage('Task not found');
            $response->send();
            exit;
        }
        //kreiramo response za pozitivan kao i kod GET po id
        while ($row = $result->fetch_assoc()) {
            $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);
            $taskArray[] = $task->returnTaskArray();
        }
        //kreiramo niz koji cemo vratiti korisniku
        $returnData = array();
        $returnData['row_returned'] = $rowCount;
        $returnData['tim'] = $taskArray; //vrati mi podatke (objekte)
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
        //da li je application/JSON

        //pogresan tip podataka za objekat koji prosledjuje
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Content type header not set to JSON');
            $response->send();
            exit;
        }
        //da li je JSON format
        $rawPostData = file_get_contents('php://input');
        if (!$jsonData = json_decode($rawPostData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Request body is not valid JSON');
            $response->send();
            exit;
        }
        //da li sva OBAVEZNA polja popunjena
        //proveravamo da li su setovana obavezna polja
        if (!isset($jsonData->nazivTima) || !isset($jsonData->drzava) || !isset($jsonData->brojTitula) || !isset($jsonData->godinaOsnivanja)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            if (!isset($jsonData->nazivTima))
                $response->addMessage('Naziv Tima filed is mandatory and must be provided');
            if (!isset($jsonData->description))
                $response->addMessage('Drzava filed is mandatory and must be provided');
            if (!isset($jsonData->brojTitula))
                $response->addMessage('Broj Titula filed is mandatory and must be provided');
            if (!isset($jsonData->godinaOsnivanja))
                $response->addMessage('Godina Osnivanja filed is mandatory and must be provided');
            $response->send();
            exit;
        }

        $newTask = new Task(
            null, // za tim id
            $jsonData->nazivTima,
            $jsonData->drzava,
            $jsonData->brojTitula,
            $jsonData->godinaOsnivanja
        );

        $nazivTima = $newTask->getNazivTima();
        $drzava = $newTask->getDrzava();
        $brojTitula = $newTask->getBrojTitula();
        $godinaOsnivanja = $newTask->getGodinaOsnivanja();

        $query = "INSERT INTO tim (nazivTima, drzava, brojTitula, godinaOsnivanja) VALUES ('$nazivTima', '$drzava', '$brojTitula','$godinaOsnivanja')";
        $result = $conn->query($query);

        $rowCount = $conn->affected_rows;
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Task not saved");
            $response->send();
            exit;
        }

        $lastTaskId = $conn->insert_id;

        $query2 = "SELECT * FROM tim WHERE timID=$lastTaskId";
        $result2 = $conn->query($query2);

        $rowCount = $result2->num_rows;
        if ($rowCount == 0) {
            $response = new Response();
            $response->setHttpStatusCode(500);
            $response->setSuccess(false);
            $response->addMessage("Failed to retive task after creation");
            $response->send();
            exit;
        }

        $row = $result2->fetch_assoc();
        $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);

        $returnData = array();
        $returnData['rows_returned'] = $rowCount;
        $returnData['task'] = $task->returnTaskArray();

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Task creted");
        $response->setData($returnData);
        $response->send();
        exit;
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage('Request method not allowed.');
        $response->send();
        exit;
    }
}
