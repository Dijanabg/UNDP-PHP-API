<?php

require_once "../model/Task.php";
$task = new Task(1, "Partizan", "Srbija", 101, 1933);
header("Content-type: application/json; charset=utf-8");
echo json_encode($task->returnTaskArray());
