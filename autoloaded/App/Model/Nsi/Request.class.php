<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Request {
    public int|null $id;
    public DateTime $dt; 
    public string $ip;

    public static function fetch(DateTime $since): array {
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    dt,
    ip
FROM
    nsi_requests
WHERE dt > TIMESTAMP(?, ?);
EOF
        )->execute([$since->format("Y-m-d"), $since->format("H:i:s")]);

        $requests = [];
        foreach ($responses as $response) {
            $request = new Request();
            $request->id = $response->id;
            $request->dt = DateTime::createFromFormat('Y-m-d H:i:s', $response->dt);
            $request->ip = implode(".", array_map("ord", str_split($response->ip)));
            array_push($requests, $request);
        }

        return $requests;
    }

    public static function has_requested(DateTime $since, string $ip): bool {
        $ipInts = explode(".", $ip);
        $ipBytes = 
              chr(intval($ipInts[0]))
            . chr(intval($ipInts[1]))
            . chr(intval($ipInts[2]))
            . chr(intval($ipInts[3]));
        
        $responses = SqlRequest::new(<<< EOF
SELECT
    id
FROM
    nsi_requests
WHERE dt > TIMESTAMP(?, ?) AND ip = ?;
EOF
        )->execute([$since->format("Y-m-d"), $since->format("H:i:s"), $ipBytes]);

        return !empty($responses);
    }

    public static function save($ip) {
        $ipInts = explode(".", $ip);
        $ipBytes = 
              chr(intval($ipInts[0]))
            . chr(intval($ipInts[1]))
            . chr(intval($ipInts[2]))
            . chr(intval($ipInts[3]));
        SqlRequest::new(<<< EOF
INSERT INTO
    nsi_requests
(
    dt,
    ip
) VALUES (
    TIMESTAMP(?, ?),
    ?
);
EOF
        )->execute([(new DateTime())->format("Y-m-d"), (new DateTime())->format("H:i:s"), $ipBytes]);
    }
}
