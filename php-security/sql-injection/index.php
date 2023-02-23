<?php

$conn = new mysqli('localhost', 'root', '', 'database_name');

// http://localhost/test/index.php?product_id=2'; DELETE FROM product WHERE product_id = '4
if(empty($_GET['id'])) {
	var_dump('nije unet parametar');
    exit;
}

// kastovanje
$id = (int) $_GET['id']; // 15

// optimizacija: sprečavamo nepotreban upit
if($id <= 0) {
	var_dump('ovo nije validan id');
    exit;
}

// sada je upit siguran
$sql = "SELECT * FROM product WHERE product_id= '$id';";
var_dump($sql);
$result = $conn->multi_query($sql);

var_dump($result);
?>
