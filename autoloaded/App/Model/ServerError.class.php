<?php
namespace App\Model;

use Exception;

class ServerError {
    // Constructeur statique
    public static function new(Exception $exception): ServerError {
        return new ServerError($exception);
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

    public function getErrorType(): string {
        return get_class($this->exception);
    }

    public function getMessage(): string {
        return $this->exception->getMessage();
    }
}