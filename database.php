<?php
$host = "localhost";    // naam van de host
$dbname = "db";     // naam van de database
$username = "root";     // gebruikersnaam van de database
$password = "supergeheim";         // wachtwoord van de database


// Maak een nieuwe PDO-verbinding met de database
try{
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch     (PDOException $e){ 
    // handle errors
    echo "foutmelding: "  . $e->getMessage();

}


//close connection

$pdo = null; 



?>