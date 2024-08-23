<?php
namespace App\Model;

use Exception;

class InternalError {
    // Constructeur statique
    public static function new(Exception $exception): InternalError {
        return new InternalError($exception);
    }

    // Attributs
    private Exception $exception;

    // Constructeur
    private function __construct(Exception $exception) {
        $this->exception = $exception;
    }

    // Getteurs
    public function getCode(): int|string|null {
        return $this->exception->getCode();
    }

    public function getMessage(): string {
        return $this->exception->getMessage();
    }
}