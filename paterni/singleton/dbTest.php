<?php
require("db.php");


$instance1 = ConnectDb::getInstance();
$conn1 = $instance1->getConnection();
print_r($conn1);

$instance2 = ConnectDb::getInstance();
$conn2 = $instance2->getConnection();
print_r($conn2);

$instance3 = ConnectDb::getInstance();
$conn3 = $instance3->getConnection();
print_r($conn3);