<?php

namespace App\Model\Quiz;

use App\Request\SqlRequest;

class Category {
    // Attributes
    public int $id;
    public string $label;

    // Static constructors
    public static function fetchAll(): array {
        // Requête de la catégorie d'ID fourni.
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    label
FROM
    api_quiz_categories;
EOF
        )->execute();

        $categories = array();

        // Pour chaque question dans la réponse...
        foreach ($responses as $response) {
            // Instanciation de la classe bâtiment.
            $category = new Category();

            // Mise à jour des attributs accessibles.
            $category->id = $response->id;
            $category->label = $response->label;

            // Stockage dans le tableau de retour de méthode.
            $categories[$category->id] = $category;
        }

        return $categories;
    }
}