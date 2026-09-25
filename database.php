<?php
// database.php - Backward compatibility / snelle test script
require_once __DIR__ . '/config/Database.php';

try {
    $pdo = Database::getConnection();
    $query = "SELECT id, name, email, role FROM users";
    $statement = $pdo->query($query);
    $users = $statement->fetchAll();

    echo "<h2>MBO Soccer - Database Verbinding Test</h2>";
    echo "<p style='color: green;'>✓ Succesvol verbonden via Singleton Database (PDO)!</p>";
    
    if (count($users) > 0) {
        echo "<ul>";
        foreach ($users as $user) {
            echo "<li><strong>" . htmlspecialchars($user['name']) . "</strong> (" . htmlspecialchars($user['email']) . ") - Rol: " . htmlspecialchars($user['role']) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Geen gebruikers gevonden in de database.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='color: red;'>Foutmelding: " . htmlspecialchars($e->getMessage()) . "</p>";
}