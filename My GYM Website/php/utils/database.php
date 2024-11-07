<?php
class Database {
    private static $conn = null;
    
    public static function getConnection() {
        if (self::$conn === null) {
            require_once __DIR__ . '/../db_config.php';
            try {
                self::$conn = new PDO(
                    "mysql:host=$host;dbname=$dbName;charset=utf8mb4",
                    $dbUsername,
                    $dbPassword,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception('Database connection failed');
            }
        }
        return self::$conn;
    }

    public static function beginTransaction() {
        self::getConnection()->beginTransaction();
    }

    public static function commit() {
        self::getConnection()->commit();
    }

    public static function rollback() {
        self::getConnection()->rollBack();
    }

    public static function prepare($sql) {
        return self::getConnection()->prepare($sql);
    }

    public static function lastInsertId() {
        return self::getConnection()->lastInsertId();
    }
}
?>
