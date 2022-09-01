<?php
namespace App\Request;

use Exception;
use PDO;

class SqlRequest {
    // Constructeur statique
    public static function new(string $sqlScript): SqlRequest {
        return new SqlRequest($sqlScript);
    }

    // Attributs
    private PDO $pdo;
    private string $sqlScript;

    // Constructeur
    public function __construct(string $sqlScript) {
        try {
            $this->pdo = new PDO(
                "mysql:host=" . getenv("SECRET_SQL_SERVER") . ';dbname=' . getenv("SECRET_SQL_DB"),
                getenv("SECRET_SQL_USER"),
                getenv("SECRET_SQL_PASSWORD")
            );
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->query('SET NAMES UTF8MB4'); // UTF8mb4 : Pour pouvoir encoder des émojis
        } catch (Exception $exception) {
            echo $exception->getMessage();
        }
        $this->sqlScript = $sqlScript;
    }

    // Méthodes
    public function execute(array $variables = array(), int $max = 0): array {
        $prepare = $this->pdo->prepare($this->sqlScript . ($max != 0 ? " LIMIT $max" : ""));

        try {
            foreach ($variables as $index => $variable) {
                if (is_bool($variable)) $type = PDO::PARAM_BOOL;
                elseif (is_int($variable)) $type = PDO::PARAM_INT;
                elseif (is_null($variable)) $type = PDO::PARAM_NULL;
                else $type = PDO::PARAM_STR;

                $prepare->bindValue($index + 1, $variable, $type);
            }

            $prepare->execute();

            $results = array();
            foreach ($prepare->fetchAll() as $index => $item) {
                $item["count"] = $index;
                $results[] = (object) $item;
            }
        } catch (Exception $exception) {
            echo $exception->getMessage();
            $results = array();
        } finally {
            $prepare->closeCursor();
        }

        return $results;
    }
}