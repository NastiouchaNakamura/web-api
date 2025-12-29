<?php
namespace App\Model;

use Throwable;

class ServerError {
    // Constructeur statique
    public static function new(Throwable $throwable): ServerError {
        return new ServerError($throwable);
    }

    // Attributs
    private Throwable $throwable;

    // Constructeur
    private function __construct(Throwable $throwable) {
        $this->throwable = $throwable;
    }

    // Getteurs
    public function getCode(): int|string|null {
        return $this->throwable->getCode();
    }

    public function getErrorType(): string {
        return get_class($this->throwable);
    }

    public function getMessage(): string {
        return $this->throwable->getMessage();
    }
}