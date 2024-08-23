<?php
namespace App\Request;

use PDO;
use PDOException;

class SqlRequest {
    // Constructeur statique
    /**
     * Create new SqlRequest instance
     * Environment variables "SECRET_SQL_SERVER", "SECRET_SQL_DB", "SECRET_SQL_USER" and "SECRET_SQL_PASSWORD" have to
     * be set before call
     * @param string $sqlScript SQL script to be executed
     * @return SqlRequest the SqlRequest instance ready for use
     * @throws PDOException
     */
    public static function new(string $sqlScript): SqlRequest {
        return new SqlRequest($sqlScript);
    }

    // Attributs
    private PDO $pdo;
    private string $sqlScript;

    // Constructeur
    /**
     * See SqlRequest::new for documentation
     */
    private function __construct(string $sqlScript) {
        $this->pdo = new PDO(
            "mysql:host=" . SECRET_SQL_SERVER
            . ';port=' . SECRET_SQL_PORT
            . ';dbname=' . SECRET_SQL_DB,
            SECRET_SQL_USER,
            SECRET_SQL_PASSWORD
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->query('SET NAMES UTF8MB4');// UTF8mb4 : Pour pouvoir encoder des émojis
        $this->sqlScript = $sqlScript;
    }

    // Méthodes

    /**
     * Executes the SQL script given on construct into the database
     * @param array $variables array of variables: each "?" in the SQL script will be replaced by the values of the
     * array, respecting array sorting
     * @param int $max maximum returned items; 0 for no maximum, which is default
     * @return array array of returned items of type object, with attributes corresponding to SQL request and additional
     * "count" to count the items
     * @throws PDOException
     */
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
        } finally {
            $prepare->closeCursor();
        }

        return $results;
    }
}