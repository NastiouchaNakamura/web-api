<?php

namespace App\Model\Quiz;

use App\Request\SqlRequest;
use DateTime;

class Question {
    // Attributes
    public int $id;
    public Category $category;
    public int $difficulty;
    public string $statement;
    public array $answers;
    public string $explanation;
    public string|null $source;
    public DateTime $lastUpdated;

    // Static constructors
    public static function fetchById(array $ids): array {
        if (count($ids) == 0) return array();

        $unpreparedArray = "(?" . str_repeat(",?", count($ids) - 1) . ")";
        $responses = SqlRequest::new(<<< EOF
SELECT
    api_quiz_questions.id AS id,
    id_category,
    label AS label_category,
    difficulty,
    statement,
    answers,
    explanation,
    source,
    last_updated
FROM
    api_quiz_questions JOIN api_quiz_categories ON api_quiz_categories.id = api_quiz_questions.id_category
WHERE
    api_quiz_questions.id IN $unpreparedArray;
EOF
        )->execute($ids);

        $questions = array();
        foreach ($responses as $response) {
            $question = new Question();

            $question->id = $response->id;
            $question->difficulty = $response->difficulty;
            $question->statement = $response->statement;
            $question->answers = explode("||", $response->answers);
            $question->explanation = $response->explanation;
            $question->source = $response->source;
            $question->lastUpdated = new DateTime($response->last_updated);

            $question->category = new Category();
            $question->category->id = $response->id_category;
            $question->category->label = $response->label_category;

            $questions[$question->id] = $question;
        }

        return $questions;
    }
}