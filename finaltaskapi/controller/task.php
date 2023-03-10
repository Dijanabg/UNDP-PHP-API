<?php

require("db.php");
require("../model/Response.php");
require("../model/Task.php");

$conn = DB::connectDB();

// tasks GET
// tasks POST
// tasks/1  PUT/PATCH
// tasks/1 GET
// tasks/1 DELETE


//.htaccess
//regularni izrazi

//Autorizacija krece
// provera da li je access token obezbedjen u http autentifikacionom header-u i da je vrednost veca od 0 karaktera
if (!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    $response->addMessage('Authorization token cannot be blank or must be set');
    $response->send();
    exit;
}
// prihvatanje access tokena iz autentifikacionog header-a - koristi se za delete (log out) i patch (refresh)
$accesstoken = $_SERVER['HTTP_AUTHORIZATION'];

try {
    //citamo podatke user-a
    // provera da li je access token jednak prosledjenom
    $query = "SELECT userid, accessexpiry, loginattempts FROM tblusers, tblsessions WHERE tblsessions.userid = tblusers.id AND accesstoken = '$accesstoken'";
    $result = $conn->query($query);

    $rowCount = mysqli_num_rows($result);
    if ($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage('Access token not valid');
        $response->send();
        exit;
    }

    $row = $result->fetch_assoc();
    ///cuvanje povratnih vrednosti
    $db_userid = $row['userid'];
    $db_accessexpiry = $row['accessexpiry'];
    $db_loginattempts = $row['loginattempts'];
// provera da li je nalog zakljucan
    if($db_loginattempts >= 3) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("User account is currently locked out");
        $response->send();
        exit;
    }
// provera da li je access token istekao
    if (strtotime($db_accessexpiry) < time()) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage('Access token expired');
        $response->send();
        exit;
    }

} catch (Exception $ex) {
    //response
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage('Issue during authentication');
    $response->send();
    exit;
}
// Autorizacije zavrsena

// u okviru ovog if/elseif statement-a bitan je tacan redosled (ako se GET koristi u vise ruta)

// provera da li je taskid u url-u kao npr. /tasks/1


if (isset($_GET['taskid'])) {
// uzimanje task id iz query string-a
    $taskid = $_GET['taskid'];
// provera da li task id u query string-u nije prazan i da li je broj, ako nije vratiti json error 
    if (!is_numeric($taskid) || $taskid == '') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage('Task ID cannot be blank or must be numberic');
        $response->send();
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $query = "SELECT * FROM tasks where id=$taskid AND userid=$db_userid";
        $result = $conn->query($query);

        $rowCount = $result->num_rows;
// task niz za skladistenje povratnog taska
        $taskArray = array();

        if ($rowCount == 0) {
// setovanje response-a za neuspesni return
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Task not found");
            $response->send();
            exit;
        }
// kreiranje novog task objekta za svaki red
        while ($row = $result->fetch_assoc()) {
        $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
// kreiranje i skladistenje taska u niz za return u json oblik
        $taskArray[] = $task->returnTaskArray();
        }
//smestanje dobijenih taskova i redova u niz da bi se vratilo u json obliku
        $returnData = array();
        $returnData['row_retured'] = $rowCount;
        $returnData['tasks'] = $taskArray;
// setovanje response-a za uspesan return
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        $query = "DELETE FROM tasks WHERE id=$taskid AND userid=$db_userid";
        $result = $conn->query($query);

        $num_rows = $conn->affected_rows;
        if ($num_rows === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Task not found.");
            $response->send();
            exit;
        }

        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Task deleted.");
        $response->send();
        exit;
    } elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
        //ovde pisemo kod za patch
        //treba da pronadjemo element sa tim id-jem
        //treba da azuriramo samo one podatke koje je korisnik uneo
        if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Content type header not set to JSON');
            $response->send();
            exit;
        }

        $rawPatchData = file_get_contents('php://input');
        if (!$jsonData = json_decode($rawPatchData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Request body is not valid JSON");
            $response->send();
            exit;
        }

        //vraca stari task sa starim vrednostim
        $query = "SELECT * FROM tasks WHERE id=$taskid";
        $result = $conn->query($query);
        $rowCount = $result->num_rows;
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No task found to update");
            $response->send();
            exit;
        }

        $title_update = false;
        $description_update = false;
        $deadline_update = false;
        $completed_update = false;

        $queryFields = "";
        if (isset($jsonData->title)) {
            $title_update = true;
            $queryFields .= "title='$jsonData->title',";
        }
        if (isset($jsonData->description)) {
            $description_update = true;
            $queryFields .= "description='$jsonData->description',";
        }
        if (isset($jsonData->deadline)) {
            $deadline_update = true;
            $queryFields .= "deadline='$jsonData->deadline',";
        }
        if (isset($jsonData->completed)) {
            $completed_update = true;
            $queryFields .= "completed='$jsonData->completed',";
        }

        if ($title_update === false && $description_update === false && $deadline_update === false && $completed_update === false) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("No task fields provided");
            $response->send();
            exit;
        }
        // var_dump($queryFields);
        // brisanje zareza i prateceg prostora
        $queryFields = rtrim($queryFields, ",");
        $queryString = "UPDATE tasks SET $queryFields WHERE id=$taskid AND userid=$db_userid";
        $result2 = $conn->query($queryString);

        //sta ako nije lepo azuriran red u tabeli

        $row = $result->fetch_assoc();

        $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);

        $queryFieldsCheck = "";
        if ($title_update) {
            $task->setTitle($jsonData->title);
            $queryFieldsCheck .= "title='{$task->getTitle()}' AND ";
        }
        if ($description_update) {
            $task->setDescription($jsonData->description);
            $queryFieldsCheck .= "description='{$task->getDescription()}' AND ";
        }
        if ($deadline_update) {
            $task->setDeadline($jsonData->deadline);
            $queryFieldsCheck .= "deadline='{$task->getDeadline()}' AND ";
        }
        if ($completed_update) {
            $task->setCompleted($jsonData->completed);
            $queryFieldsCheck .= "completed='{$task->getCompleted()}' AND ";
        }
        $queryFieldsCheck .= "id='{$task->getID()}'";

        $query3 = "SELECT * FROM tasks WHERE $queryFieldsCheck";
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
        //SELECT * FROM tasks WHERE title='...' AND description='...' AND id=...
//potrebno je napisati upit koji proverava da li postoji element sa: id, deadline, completed, title, description

        $result4 = $conn->query("SELECT * FROM tasks WHERE id=$taskid AND userid=$db_userid");
        $rowCount = $result4->num_rows;
// provera da li je red pronadjen
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No task found");
            $response->send();
            exit;
        }

        $row = $result4->fetch_assoc();
        $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);

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
} elseif (isset($_GET['completed'])) {
    $completed = $_GET['completed'];

    if ($completed !== 'Y' && $completed !== 'N') {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Completed filter must be Y or N");
        $response->send();
        exit;
    }
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        $query = "SELECT * FROM tasks WHERE completed='$completed' AND userid = $db_userid";
        $result = $conn->query($query);
        $rowCount = $result->num_rows;
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("No task found");
            $response->send();
            exit;
        }

        while ($row = $result->fetch_assoc()) {
            $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
            $taskArray[] = $task->returnTaskArray();
        }

        $returnData = array();
        $returnData['row_returned'] = $rowCount;
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
} elseif (isset($_GET['page'])) {
    //kod
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
// get page id from query string
        $page = $_GET['page'];
//provera da li id stranice nije prazan i da li je broj, ako nije vraca se json error
        if ($page == '' || !is_numeric($page)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Page number connot be blank and must be numeric!");
            $response->send();
            exit;
        }
// postavljanje limita na 10 po strani
        $limitPerPage = 10;
        $query = "SELECT count(id) as totalNoOfTasks FROM tasks WHERE userid=$db_userid";
        $result = $conn->query($query);

        $row = mysqli_fetch_assoc($result);
        $tasksCount = intval($row['totalNoOfTasks']); //ukupan broj taskova u tabeli
        // $tasksCount = intval($row['count(id)']);
// broj stranica potreban za krajnji rezultat, koristiti ceil za zaokruzivanje
        $numOfPages = ceil($tasksCount / $limitPerPage);
        //ako imamo 21 task u tabeli
        // 1  2  2,1 - NE
        // 1  2  3 - DA
        // 10 10 1 - da bi bilo ovako
// ako se ne vrati nijedan red onda uvek dozvoli stranici 1 da prikaze uspesan odgovor sa 0 taskova
        if ($numOfPages == 0) {
            $numOfPages = 1;
        }
        // tasks/page/100 // ako je prosledjeni broj stranica veci od ukupnog broja dostupnih stranica ili ima 0 stranica onda ???404 error - page not found???
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
        $query = "SELECT * FROM tasks WHERE userid = $db_userid limit $limitPerPage offset $offset";
        $result2 = $conn->query($query);

        $rowCount = $result2->num_rows;
        while ($row = $result2->fetch_assoc()) {
            $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
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
} elseif (empty($_GET)) {
    //radi se get all
    //vracamo sve -> vracali 1
    //petlju -> nema petlje
    //200 ok
    //404 
    if ($_SERVER['REQUEST_METHOD'] === "GET") {
        // vraca sve task-ove korisnika koji je ulogovan
        $query = "SELECT * FROM tasks WHERE userid = $db_userid";
        $result = $conn->query($query);

        $rowCount = $result->num_rows;
        if ($rowCount === 0) {
            $response = new Response();
            $response->setHttpStatusCode(404);
            $response->setSuccess(false);
            $response->addMessage("Tasks not found!");
            $response->send();
            exit;
        }

        while ($row = $result->fetch_assoc()) {
            $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);
            $taskArray[] = $task->returnTaskArray();
        }

        $returnData = array();
        $returnData['row_returned'] = $rowCount;
        $returnData['tasks'] = $taskArray;
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->setData($returnData);
        $response->send();
    } elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
        //da li je application/JSON
        //da li je JSON format
        //da li sva OBAVEZNA polja popunjna
        //pogresan tim podataka za objekat koji prosledjuje

        if ($_SERVER['CONTENT_TYPE'] !== "application/json") {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage("Content type header not set to JSON");
            $response->send();
            exit;
        }

        $rawPostData = file_get_contents('php://input');
        if (!$jsonData = json_decode($rawPostData)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            $response->addMessage('Request body is not valid JSON');
            $response->send();
            exit;
        }

        //proveravamo da li su setovana obavezna polja
        if (!isset($jsonData->title) || !isset($jsonData->description) || !isset($jsonData->completed)) {
            $response = new Response();
            $response->setHttpStatusCode(400);
            $response->setSuccess(false);
            if (!isset($jsonData->title))
                $response->addMessage('Title filed is mandatory and must be provided');
            if (!isset($jsonData->description))
                $response->addMessage('Description filed is mandatory and must be provided');
            if (!isset($jsonData->completed))
                $response->addMessage('Completed filed is mandatory and must be provided');
            $response->send();
            exit;
        }
// kreiranje novog taska sa podacima, ako obavezna polja nisu podesena setovati na nulu
        $newTask = new Task(
            null,
            $jsonData->title,
            $jsonData->description,
            !isset($jsonData->deadline) ? null : $jsonData->deadline,
            $jsonData->completed
        );

        $title = $newTask->getTitle();
        $description = $newTask->getDescription();
        $deadline = $newTask->getDeadline();
        $completed = $newTask->getCompleted();
        if ($deadline == null)
            $query = "INSERT INTO tasks (title, description, deadline, completed, userid) VALUES ('$title', '$description', null,'$completed', $db_userid)"; //izmenjeno
        else
            $query = "INSERT INTO tasks (title, description, deadline, completed, userid) VALUES ('$title', '$description', '$deadline','$completed', $db_userid)"; //izmenjeno
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

        $query2 = "SELECT * FROM tasks WHERE id=$lastTaskId AND userid=$db_userid";
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
        $task = new Task($row['id'], $row['title'], $row['description'], $row['deadline'], $row['completed']);

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
        $response->addMessage("Request method not allowed");
        $response->send();
        exit;
    }
}
