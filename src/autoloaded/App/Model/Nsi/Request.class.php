<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Request {
    public int|null $id;
    public DateTime $dt; 
    public string $ip;
    public string $challenge_id;

    public static function fetch(DateTime $since): array {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    dt,
    ip,
    challenge_id
FROM
    api_nsi_requests
WHERE dt > TIMESTAMP(?, ?);
EOF
        )->execute([$since->format("Y-m-d"), $since->format("H:i:s")]);

        $requests = [];
        foreach ($responses as $response) {
            $request = new Request();
            $request->id = $response->id;
            $request->dt = DateTime::createFromFormat('Y-m-d H:i:s', $response->dt);
            $request->ip = implode(".", array_map("ord", str_split($response->ip)));
            $request->challenge_id = $response->challenge_id;
            array_push($requests, $request);
        }

        return $requests;
    }

    public static function has_requested(DateTime $since, string $ip): bool {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id
FROM
    api_nsi_requests
WHERE dt > TIMESTAMP(?, ?) AND ip = ?;
EOF
        )->execute([$since->format("Y-m-d"), $since->format("H:i:s"), $ip]);

        return !empty($responses);
    }

    public static function save($ip, $challenge_id) {
        SqlRequest::new(<<< EOF
INSERT INTO
    api_nsi_requests
(
    dt,
    ip,
    challenge_id
) VALUES (
    TIMESTAMP(?, ?),
    ?,
    ?
);
EOF
        )->execute([(new DateTime())->format("Y-m-d"), (new DateTime())->format("H:i:s"), $ip, $challenge_id]);
    }
}
