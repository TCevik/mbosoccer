<?php
// config/Database.php
// Deze class regelt de verbinding met de MySQL database via PDO.

class Database {
    // Hier slaan we de verbinding in op, zodat we niet steeds een nieuwe verbinding openen
    private static $conn = null;

    // Deze functie maakt verbinding met de database als dat nog niet is gebeurd
    public static function connect(): PDO {
        if (self::$conn === null) {
            // Gegevens van de database (draait in Docker)
            $host = 'db';
            $dbname = 'phples';
            $username = 'root';
            $password = 'supergeheim';

            // Verbinding opbouwen met PDO
            self::$conn = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    // Zorgt dat fouten netjes als exceptions worden gemeld
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    // Zorgt dat data als gewone array met kolomnamen terugkomt
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
        }
        return self::$conn;
    }

    // Extra functie zodat getConnection() hetzelfde doet als connect()
    public static function getConnection(): PDO {
        return self::connect();
    }
}
