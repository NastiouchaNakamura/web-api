<?php

namespace App\Model;

class Error {
    // Constructeur statique
    public static function new(string|int $code, string $message): Error {
        return new Error($code, $message);
    }

    // Attributs
    private string|int $code;
    private string $message;

    // Constructeur
    private function __construct(string|int $code, string $message) {
        $this->code = $code;
        $this->message = $message;
    }

    // Getteurs
    public function getCode(): int|string {
        return $this->code;
    }

    public function getMessage(): string {
        return $this->message;
    }
}