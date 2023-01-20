<?php

class DB
{
    private static $conn;

    public static function connectDB()
    {
        //singleton pattern koristi se kada jedna konekcija na bazu je zajednicka za sve, recimo usere
        if (self::$conn == null) {
            self::$conn = new mysqli('localhost', 'root', '', 'fudbalskitimovi');
            return self::$conn;
        }
    }
}
