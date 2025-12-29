<?php

namespace App\Model\Nsi;

use App\Model\Color;
use App\Request\SqlRequest;

class Profile {
    public string $id;
    public string $username;
    public string $pw_hash;
    public Color $color;

    public static function fetchByUsername(string $username): Profile|null {
        $username = str_replace(" ", "", trim($username));
        if (empty($username)) return null;
        
        $responses = SqlRequest::new(<<< EOF
SELECT
    id,
    username,
    pw_hash,
    color_hex
FROM
    api_nsi_profiles
WHERE username = ?;
EOF
        )->execute(["$username"]);

        if (empty($responses)) {
            return null;
        } else {
            $profile = new Profile();
            $profile->id = $responses[0]->id;
            $profile->username = $responses[0]->username;
            $profile->pw_hash = $responses[0]->pw_hash;
            $profile->color = new Color(
                hexdec(substr($responses[0]->color_hex, 1, 2)),
                hexdec(substr($responses[0]->color_hex, 3, 2)),
                hexdec(substr($responses[0]->color_hex, 5, 2)));
            return $profile;
        }
    }
}
