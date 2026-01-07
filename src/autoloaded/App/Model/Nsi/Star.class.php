<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Star {
    public string $challenge_id;
    public string $challenge_title;
    public DateTime $dt;
    public string $type;
    public int $amount;

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
