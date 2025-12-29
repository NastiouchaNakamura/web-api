<?php
namespace App\Model;

class UserError {
    // Constructeur statique
    public static function new(string $message): UserError {
        return new UserError($message);
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