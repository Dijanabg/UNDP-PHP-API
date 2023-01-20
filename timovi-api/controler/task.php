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
    //     elseif ($_SERVER['REQUEST_METHOD'] === "DELETE") {
    //         $query = "DELETE FROM timovi WHERE timId=$taksid";
    //         $result = $conn->query($query);
    //         //proveravamo moguce greske, recimo da u tabeli nema tog id
    //         $num_rows->$conn->affected_rows;
    //         if ($num_rows === 0) {
    //             $response = new Response();
    //             $response->setHttpStatusCode(404);
    //             $response->setSuccess(false);
    //             $response->addMessage('Taks not found.');
    //             $response->send();
    //             exit;
    //         }
    //         //kreiramo response za pozitivan
    //         $response = new Response();
    //         $response->setHttpStatusCode(200);
    //         $response->setSuccess(true);
    //         $response->addMessage('Taks delete');
    //         $response->send();
    //         exit;
    //     }
    //     //ovde pisemo kod za patch
    //     elseif ($_SERVER['REQUEST_METHOD'] === "PATCH") {
    //         //treba da nadjemo id elementa koji zelimo da izmenimo
    //         $rowPatchData = file_get_contents('php://input'); //kupimo podatke iz input polja
    //         if (!$jsonData = json_decode($rowPatchData)) { //probamo da dekodiramo podatke u json, provera validnosti
    //             //ako podaci nisu dobri ili su null treba da baci error
    //             $response = new Response();
    //             $response->setHttpStatusCode(400);
    //             $response->setSuccess(false);
    //             $response->addMessage('Request body not valid json');
    //             $response->send();
    //             exit;
    //         }
    //         //greska ukoliko ne postoji taj id element
    //         $query = "SELECT * FROM timovi WHERE timId= $taskid";
    //         $result = $conn->query($query);

    //         $rowCount = $result->num_rows;
    //         //sta ako je broj redova koji je vracen jednak nuli
    //         if ($rowCount === 0) {
    //             $response = new Response();
    //             $response->setHttpStatusCode(404);
    //             $response->setSuccess(false);
    //             $response->addMessage('No task found to update');
    //             $response->send();
    //             exit;
    //         }
    //         //ako je pozitivno stavljamo da po difoltu nijedan nije za azuriranje
    //         $nazivTima = false;
    //         $drzava = false;
    //         $brojTitula = false;
    //         $godinaOsnivanja = false;
    //         //polja za azuriranje
    //         $queryFields = "";
    //         //dekodiranjem smo pretvorili u json objekat
    //         //podaci koje pratimo da li su azurirani
    //         if (isset($jsonData->nazivTima)) {
    //             $nazivTima_update = true;
    //             $queryFields .= "nazivTima='$jsonData->nazivTima',";
    //         }
    //         if (isset($jsonData->drzava)) {
    //             $drzava_update = true;
    //             $queryFields .= "drzava='$jsonData->drzava',";
    //         }
    //         if (isset($jsonData->brojTitula)) {
    //             $brojTitula_update = true;
    //             $queryFields .= "brojTitula='$jsonData->brojTitula',";
    //         }
    //         if (isset($jsonData->godinaOsnivanja)) {
    //             $godinaOsnivanja_update = true;
    //             $queryFields .= "godinaOsnivanja='$jsonData->godinaOsnivanja',";
    //         }
    //         //ako nista nije setovano za azuriranje a da ne bi izbrisao objekat
    //         if ($nazivTima === false && $drzava === false && $brojTitula === false && $godinaOsnivanja === false) {
    //             $response = new Response();
    //             $response->setHttpStatusCode(400);
    //             $response->setSuccess(false);
    //             $response->addMessage("No task fields provided");
    //             $response->send();
    //             exit;
    //         }
    //         //hvatamo podatke za ispis iz baze
    //         $queryFields = rtrim($queryFields, ","); //uklanja poslednji zarez u nizu $queryFields
    //         $queryString = "UPDATE tasks SET $queryFields WHERE id=$taskid";
    //         $result2 = $conn->query($queryString);
    //         //sta ako nije lepo azuriran red u tabeli

    //         $row = $result->fetch_assoc();

    //         $task = new Task($row['timId'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);

    //         $queryFieldsCheck = "";
    //         if ($nazivTima_update) {
    //             $task->setNazivTima($jsonData->nazivTima);
    //             $queryFieldsCheck .= "nazivTima='{$task->getNazivTima()}' AND ";
    //         }
    //         if ($drzava_update) {
    //             $task->setDrzava($jsonData->drzava);
    //             $queryFieldsCheck .= "drzava='{$task->getDrzava()}' AND ";
    //         }
    //         if ($brojTitula_update) {
    //             $task->setBrojTitula($jsonData->brojTitula);
    //             $queryFieldsCheck .= "brojTitula='{$task->getBrojTitula()}' AND ";
    //         }
    //         if ($godinaOsnivanja_update) {
    //             $task->setGodinaOsnivanja($jsonData->godinaOsnivanja);
    //             $queryFieldsCheck .= "godinaOsnivanja='{$task->getGodinaOsnivanja()}' AND ";
    //         }
    //         $queryFieldsCheck .= "timId='{$task->getTimID()}'";

    //         $query3 = "SELECT * FROM tasks WHERE $queryFieldsCheck";
    //         $result3 = $conn->query($query3);

    //         $rowCount = $result3->num_rows;
    //         if ($rowCount === 0) {
    //             $response = new Response();
    //             $response->setHttpStatusCode(404);
    //             $response->setSuccess(false);
    //             $response->addMessage("Task not updated - given values must be the same as the stored values");
    //             $response->send();
    //             exit;
    //         }
    //         //SELECT * FROM tasks WHERE nazivTima='...' AND brojTitula='...' AND ...
    //         //potrebno je napisati upit koji proverava da li postoji element sa propertijima iz tabele
    //         //ako nema vracenih redova
    //         $result4 = $conn->query("SELECT * FROM tasks WHERE timId=$taskid");
    //         $rowCount = $result4->num_rows;
    //         if ($rowCount === 0) {
    //             $response = new Response();
    //             $response->setHttpStatusCode(404);
    //             $response->setSuccess(false);
    //             $response->addMessage("No task found");
    //             $response->send();
    //             exit;
    //         }
    //         //ako je sve u redu
    //         $row = $result4->fetch_assoc(); //uzimamo podatke i smestamo u novi task ispod
    //         $task = new Task($row['timId'], $row['nazivTima'], $row['drzava'], $row['brojTitula'], $row['godinaOsnivanja']);

    //         $taskArray[] = $task->returnTaskArray();
    //         $returnData = array();
    //         $returnData['row_returned'] = $rowCount;
    //         $returnData['task'] = $taskArray;

    //         $response = new Response();
    //         $response->setHttpStatusCode(200);
    //         $response->setSuccess(true);
    //         $response->addMessage('Task updated');
    //         $response->setData($returnData);
    //         $response->send();
    //         exit;
    //     } else {
    //         $response = new Response();
    //         $response->setHttpStatusCode(405);
    //         $response->setSuccess(false);
    //         $response->addMessage('Request method not allowed');
    //         $response->send();
    //         exit;
    //     }
} //ovde pisemo GET all da ispise sve taskove, zato sto nam ne treba id
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
    } else {
        $response = new Response();
        $response->setHttpStatusCode(405);
        $response->setSuccess(false);
        $response->addMessage('Request method not allowed.');
        $response->send();
        exit;
    }
}
