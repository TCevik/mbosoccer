<?php
$host = "localhost";    // naam van de host
$dbname = "db";     // naam van de database
$username = "root";     // gebruikersnaam van de database
$password = "supergeheim";         // wachtwoord van de database

// Maak een nieuwe PDO-verbinding met de database
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = "SELECT * FROM users";
    $statement = $pdo->query($query);
    $users = $statement->fetchAll();

    if (count($users) > 0) {
        foreach ($users as $user) {
            echo "user ID: " . $user['id'] . ", username: " . $user['username'] . ", password: " . $user['password'] . "<br>";
        }
    } else {

        echo "no users found in the database.";
    }
} catch (PDOException $e) {
    // handle errors
    echo "foutmelding: " . $e->getMessage();
} finally {
    // close connection
    $pdo = null;
}
?>