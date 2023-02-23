<?php
// podaci sa forme

$jsonData = json_decode(file_get_contents("php://input"), true);

$ime = $jsonData['ime'];
$email = $jsonData['email'];
$tekst = $jsonData['tekst'];

// Heder za ispis pošaljioca u mail klijentu
$heder = "From: $ime <$email> \n\r";

// slanje email-a na našu adresu
if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
	// sada je sigurno poslati mail
}

?>
