<?php

namespace App\Model\Nsi;

use DateTime;
use App\Request\SqlRequest;

class Profile {
    public string $username;
    public string $pw_hash;
    public DateTime $creation_dt;
    public bool $displayable;

    public static function fetchByUsername(string $username): Profile|null {
        $username = str_replace(" ", "", trim($username));
        if (empty($username)) return null;
        
        $responses = SqlRequest::new(<<< EOF
            SELECT
                username,
                pw_hash,
                creation_dt,
                displayable
            FROM
                api_nsi_profiles
            WHERE username = ?;
            EOF
        )->execute([$username]);

        if (empty($responses)) {
            return null;
        } else {
            $profile = new Profile();
            $profile->username = $responses[0]->username;
            $profile->pw_hash = $responses[0]->pw_hash;
            $profile->creation_dt = new DateTime($responses[0]->creation_dt);
            $profile->displayable = $responses[0]->displayable != 0;
            return $profile;
        }
    }

    public static function create(string $username, string $pw_hash, string $first_name, string $last_name, string $class) {
        SqlRequest::new(<<< EOF
            INSERT INTO
                api_nsi_profiles
            (
                username,
                pw_hash,
                first_name,
                last_name,
                class,
                creation_dt,
                displayable
            ) VALUES (?, ?, ?, ?, ?, TIMESTAMP(?, ?), 1);
            EOF
        )->execute([$username, $pw_hash, $first_name, $last_name, $class, (new DateTime())->format("Y-m-d"), (new DateTime())->format("H:i:s")]);
    }

    public static function changePassword(string $username, string $pw_hash) {
        SqlRequest::new(<<< EOF
            UPDATE
                api_nsi_profiles
            SET
                pw_hash = ?
            WHERE
                username = ?;
            EOF
        )->execute([$pw_hash, $username]);
    }
}
