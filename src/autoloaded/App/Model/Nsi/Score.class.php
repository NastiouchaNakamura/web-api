<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Score {
    public string $username;
    public array $stars = array();
    public array $special_stars = array();

    public function getTotalStars() {
        return array_sum(array_map(function(Star $star) { return $star->amount; }, $this->stars));
    }

    public function getTotalBasicStars() {
        return array_sum(array_map(function(Star $star) { return $star->type == "BASIC" ? $star->amount : 0; }, $this->stars));
    }

    public function getTotalDiamondStars() {
        return array_sum(array_map(function(Star $star) { return $star->type == "DIAMOND" ? $star->amount : 0; }, $this->stars));
    }

    public function getTotalGoldStars() {
        return array_sum(array_map(function(Star $star) { return $star->type == "GOLD" ? $star->amount : 0; }, $this->stars));
    }

    public function getTotalSpecialStars() {
        return array_sum(array_map(function(Star $star) { return $star->amount; }, $this->special_stars));
    }

    public static function fetch_best_scores(int $limit): array {
        // Récupération des meilleurs profils.
        $responses = SqlRequest::new(<<< EOF
SELECT
	username,
	SUM(stars_count) AS total_stars
FROM
	(SELECT
		username,
		challenge_id,
		dt,
		star_type,
		stars_count
	FROM
		sandbox.api_nsi_stars
			JOIN
		sandbox.api_nsi_challenges
			ON sandbox.api_nsi_stars.challenge_id = sandbox.api_nsi_challenges.id
	) AS all_stars_by_username
GROUP BY
	username
ORDER BY
	total_stars
LIMIT ?;
EOF
        )->execute([$limit]);

        $best_scores = array();
        foreach ($responses as $response) {
            $best_scores[$response->username] = new Score;
            $best_scores[$response->username]->username = $response->username;
        }

        // Récupération des étoiles des profils.
        $marker_str = implode(", ", array_fill(0, count($best_scores), '?'));
        $responses = SqlRequest::new(<<< EOF
SELECT
    username,
    challenge_id,
    dt,
    star_type,
	stars_count,
    title
FROM
    sandbox.api_nsi_stars
        JOIN
    sandbox.api_nsi_challenges
        ON sandbox.api_nsi_stars.challenge_id = sandbox.api_nsi_challenges.id
WHERE username IN ($marker_str);
EOF
        )->execute(array_keys($best_scores));

        foreach ($responses as $response) {
            $star = new Star();
            $star->challenge_id = $response->challenge_id;
            $star->challenge_title = $response->title;
            $star->dt = DateTime::createFromFormat('Y-m-d H:i:s', $response->dt);
            $star->type = $response->star_type;
            $star->amount = $response->stars_count;
            array_push($best_scores[$response->username]->stars, $star);
        }

        return array_values($best_scores);
    }
}
