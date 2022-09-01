<?php

namespace App\Model;

class Error {
    // Constructeur statique
    public static function new(string|int|null $code, string $message): Error {
        return new Error($code, $message);
    }

    // Attributs
    private string|int|null $code;
    private string $message;

    // Constructeur
    private function __construct(string|int|null $code, string $message) {
        $this->code = $code;
        $this->message = $message;
    }

    // Getteurs
    public function getCode(): int|string|null {
        return $this->code;
    }

    public function getMessage(): string {
        return $this->message;
    }
}