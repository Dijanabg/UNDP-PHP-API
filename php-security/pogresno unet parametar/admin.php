<?php
// admin.php?admin=1
var_dump($_GET['admin']);
$admin = $_GET['admin'];
if (proveriAdministratora()) {
    $admin = true;
}

// Ukoliko je admin, prikazujemo mu opcije
// u suprotnom login stranu
if ($admin) {
    var_dump("pokreni admina");
    // include 'admin.php';
} else {
    var_dump("pokreni login");
    // include 'login.php';
}

function proveriAdministratora()
{
    return false;
}
