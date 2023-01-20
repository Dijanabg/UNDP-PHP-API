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


if (isset($_GET['taskid'])) {
    $taskid = $_GET['taskid'];

    //prva greska da li je id broj i da li je poslato prazno

    if (!is_numeric($taskid) || $taskid === "") {
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
        $query = "SELECT * FROM tim WHERE timId=$taskid"; //obratiti paznju timId ide iz tabele naziv kolone gde je id
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

        //while ($row = $result->fetch_assoc()) {
        $row = $result->fetch_assoc();
        $task = new Task($row['timID'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);
        $taskArray[] = $task->returnTaskArray();
        //}
        //kreiramo niz koji cemo vratiti korisniku
        $returnData = array();
        $returnData['row_retured'] = $rowCount; //koliko je redova vraceno
        $returnData['tasks'] = $taskArray; //vrati mi podatke (objekat)
        $response = new Response(); //vraca response 
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData); //vraca podatke odnosno niz returnData
        $response->send();
        exit;
    }
    //ovde pisemo delete jer je u glavnom ifu gde je get=taskid
    elseif ($_SERVER['REQUEST_METHOD'] === "DELETE") {
        $query = "DELETE FROM timovi WHERE timId=$taksid";
        $result = $conn->query($query);
        //proveravamo moguce greske, recimo da u tabeli nema tog id
        $num_rows->$conn->affected_rows;
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
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage('Request method not allowed');
        $response->send();
        exit;
    }
} //ovde pisemo GET all da ispise sve taskove, zato sto nam ne treba id
elseif (empty($_GET)) {
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $query = "SELECT * FROM timovi";
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
        $returnData['tasks'] = $taskArray; //vrati mi podatke (objekte)
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
        $response->addMessage('Request method not allowed.');
        $response->send();
        exit;
    }
}
