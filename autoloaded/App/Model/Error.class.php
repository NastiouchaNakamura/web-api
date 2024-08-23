<?php
namespace App\Model;

class Error {
    // Constructeur statique
    public static function new(string $message): Error {
        return new Error($message);
    }

    // Attributs
    private string $message;

    // Constructeur
    private function __construct(string $message) {
        $this->message = $message;
    }

    // Getteurs
    public function getMessage(): string {
        return $this->message;
    }
}