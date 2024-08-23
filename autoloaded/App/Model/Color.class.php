<?php
namespace App\Model;

class Color {
    public int $r;
    public int $g;
    public int $b;

    public function __construct(int $r = null, int $g = null, int $b = null) {
        $this->r = $r;
        $this->g = $g;
        $this->b = $b;
    }

    public function getHex(): string {
        return "#" . str_pad(base_convert($this->r * 65536 + $this->g * 256 + $this->b, 10, 16), 6, '0', STR_PAD_LEFT);
    }
}