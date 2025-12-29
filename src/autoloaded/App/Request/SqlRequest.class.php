<?php
namespace App\Request;

use PDO;
use PDOException;

class SqlRequest {
    // Constructeur statique
    /**
     * Create new SqlRequest instance
     * Environment variables "SQL_HOST", "SQL_PORT", "SQL_DATABASE", "SQL_USER" and "SQL_PASSWORD" have to be set before
     * call
     * @param string $sqlScript SQL script to be executed
     * @return SqlRequest the SqlRequest instance ready for use
     * @throws PDOException when an exception occurs on initial connection
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
            "mysql:host=" . getenv("SQL_HOST")
            . ';port=' . getenv("SQL_PORT")
            . ';dbname=' . getenv("SQL_DATABASE"),
            getenv("SQL_USER"),
            getenv("SQL_PASSWORD")
        );
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->query('SET NAMES UTF8MB4');// UTF8mb4 : Pour pouvoir encoder des émojis
        $this->sqlScript = trim(preg_replace("/\s+/", " ", $sqlScript));
    }

    // Méthodes

    /**
     * Executes the SQL script (given on construct) into the database
     * @param array $variables array of variables: each "?" in the SQL script will be replaced by the values of the
     * array, in the same order
     * @param int $max maximum returned items; defaults to 0 for no maximum
     * @return array array of returned items, each one typed as an object which attributes corresponds to the columns of
     * the SQL response, and an additional attribute "count" on each object that is the index of the object in the
     * response (starting at 0)
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
