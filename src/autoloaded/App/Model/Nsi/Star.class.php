<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Star {
    public string $challenge_id;
    public DateTime $dt;
    public string $type;

    public static function fetch_stars_of_profile(string $username): array {
        $responses = SqlRequest::new(<<< EOF
SELECT
    challenge_id,
    dt,
    star_type
FROM
    api_nsi_stars
WHERE username = ?;
EOF
        )->execute([$username]);

        $stars = array();
        foreach ($responses as $response) {
            $star = new Star();
            $star->challenge_id = $response->challenge_id;
            $star->dt = DateTime::createFromFormat('Y-m-d H:i:s', $response->dt);
            $star->type = $response->star_type;
            array_push($stars, $star);
        }

        return $stars;
    }

    public static function fetch_stars_of_profiles(array $usernames): array {
        $marker_str = implode(", ", array_fill(0, count($usernames), '?'));
        $responses = SqlRequest::new(<<< EOF
SELECT
    username,
    challenge_id,
    dt,
    star_type
FROM
    api_nsi_stars
WHERE username IN ($marker_str);
EOF
        )->execute($usernames);

        $stars_of_profiles = array_combine($usernames, array_fill(0, count($usernames), array()));
        foreach ($responses as $response) {
            $star = new Star();
            $star->challenge_id = $response->challenge_id;
            $star->dt = DateTime::createFromFormat('Y-m-d H:i:s', $response->dt);
            $star->type = $response->star_type;
            array_push($stars_of_profiles[$response->username], $star);
        }

        return $stars_of_profiles;
    }

    public static function has_been_obtained(string $username, string $challenge_id): bool {
        $responses = SqlRequest::new(<<< EOF
SELECT
    star_type
FROM
    api_nsi_stars
WHERE username = ? AND challenge_id = ?;
EOF
        )->execute([$username, $challenge_id]);

        return !empty($responses);
    }

    public static function save(string $username, string $challenge_id, string $type): void {
        SqlRequest::new(<<< EOF
INSERT INTO
    api_nsi_stars
(
    username,
    challenge_id,
    dt,
    star_type
) VALUES (
    ?,
    ?,
    TIMESTAMP(?, ?),
    ?
);
EOF
        )->execute([$username, $challenge_id, (new DateTime())->format("Y-m-d"), (new DateTime())->format("H:i:s"), $type]);
    }
}
