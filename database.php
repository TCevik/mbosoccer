<?php
$host = "localhost";    // naam van de host
$dbname = "phples";     // naam van de database
$username = "root";     // gebruikersnaam van de database
$password = "";         // wachtwoord van de database


// Maak een nieuwe PDO-verbinding met de database
try{
$pdo = new pdo("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(pdo::ATTR_ERRMODE)
}





?>