<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Request {
    public int|null $id;
    public DateTime $dt; 
    public string $ip;
    public string $challenge_id;
    public string $username;

    public static function fetch(DateTime $since): array {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    dt,
    ip,
    challenge_id,
    username
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
            $request->username = $response->username;
            array_push($requests, $request);
        }

        return $requests;
    }

    public static function has_requested_anonymously(DateTime $since, string $ip): bool {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id
FROM
    api_nsi_requests
WHERE dt > TIMESTAMP(?, ?) AND ip = ? AND username = NULL;
EOF
        )->execute([$since->format("Y-m-d"), $since->format("H:i:s"), $ip]);

        return !empty($responses);
    }

    public static function has_requested_authentified(DateTime $since, string $username): bool {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id
FROM
    api_nsi_requests
WHERE dt > TIMESTAMP(?, ?) AND username = ?;
EOF
        )->execute([$since->format("Y-m-d"), $since->format("H:i:s"), $username]);

        return !empty($responses);
    }

    public static function save(string $ip, string $challenge_id, string|null $username): void {
        SqlRequest::new(<<< EOF
INSERT INTO
    api_nsi_requests
(
    dt,
    ip,
    challenge_id,
    username
) VALUES (
    TIMESTAMP(?, ?),
    ?,
    ?,
    ?
);
EOF
        )->execute([(new DateTime())->format("Y-m-d"), (new DateTime())->format("H:i:s"), $ip, $challenge_id, $username]);
    }
}
