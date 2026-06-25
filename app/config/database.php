<?php
class Database {
    private static ?Database $instance = null;
    private PDO $pdo;

    private function __construct() {
        $host   = getenv('DB_HOST') ?: 'localhost';
        $dbname = getenv('DB_NAME') ?: 'app_tgi20260622';
        $user   = getenv('DB_USER') ?: 'root';
        $pass   = getenv('DB_PASS') ?: '';
        $port   = getenv('DB_PORT') ?: '3306';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        // Désactiver le mode strict MySQL pour compatibilité prod/dev
        // (évite "Field doesn't have a default value" sur colonnes NOT NULL sans DEFAULT)
        $this->pdo->exec("SET SESSION sql_mode = (
            SELECT REPLACE(REPLACE(REPLACE(@@SESSION.sql_mode,
                'STRICT_TRANS_TABLES',''),
                'STRICT_ALL_TABLES',''),
                ',,',',')
        )");
    }

    public static function getInstance(): self {
        if (self::$instance === null) self::$instance = new self();
        return self::$instance;
    }

    public function getPDO(): PDO { return $this->pdo; }

    public function query(string $sql, array $params = []): PDOStatement {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
