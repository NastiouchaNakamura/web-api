<?php

namespace App\Model\Nsi;

use App\Request\SqlRequest;

class Challenge {
    public string $id;
    public string $flag;

    public static function fetch(string $id): Challenge|null {
        $id = str_replace(" ", "", trim($id));
        if (empty($id)) return null;
        
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    flag
FROM
    nsi_challenges
WHERE id = ?;
EOF
        )->execute(["$id"]);

        if (empty($responses)) {
            return null;
        } else {
            $challenge = new Challenge();
            $challenge->id = $responses[0]->id;
            $challenge->flag = $responses[0]->flag;
            return $challenge;
        }
    }
}
