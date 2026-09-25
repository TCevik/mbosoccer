<?php
// config/Database.php - Singleton Database Verbinding via PDO

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $host = getenv('DB_HOST') ?: 'db';
            $port = getenv('DB_PORT') ?: '3306';
            $dbname = getenv('DB_NAME') ?: 'phples';
            $username = getenv('DB_USER') ?: 'root';
            $password = getenv('DB_PASS') ?: 'supergeheim';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false, // Essentieel tegen SQL-injectie
            ];

            try {
                self::$instance = new PDO($dsn, $username, $password, $options);
            } catch (PDOException $e) {
                // Toon veilige foutmelding
                die("Database verbindingsfout: " . htmlspecialchars($e->getMessage()));
            }
        }
        return self::$instance;
    }
}
