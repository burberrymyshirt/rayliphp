<?php

declare(strict_types=1);

use \FFI\CData;

// TODO: This should probably just be some sort of static class modifying
// raylib vectors, as it is probably pretty expensive to keep calling the toC()
// function to use as a parameter to all the vector functions.
// It is more idiomatic php code, but that is not necessarily what wee are going for.
//
// I don't even think it matters that much, this file has just become way too messy way too quickly
class Vector2 {
    public function __construct(
        public float $x = 0.0,
        public float $y = 0.0,
    ) {}

    public function toC(): \FFI\CData {
        $c = RL_FFI->new('struct Vector2');
        $c->x = $this->x;
        $c->y = $this->y;
        return $c;
    }

    public function add(self $in): self {
        return new self($this->x + $in->x, $this->y + $in->y);
    }

    public function addInPlace(self $in): self {
        $this->x += $in->x;
        $this->y += $in->y;
        return $this;
    }

    public function scale(float $factor): self {
        return new self($this->x * $factor, $this->y * $factor);
    }

    public function scaleInPlace(float $factor): self {
        $this->x *= $factor;
        $this->y *= $factor;
        return $this;
    }

    public function mul(self $in): self {
        return new self($this->x * $in->x, $this->y * $in->y);
    }

    public function mulInPlace(self $in): self {
        $this->x *= $in->x;
        $this->y *= $in->y;
        return $this;
    }

    public function normalize(): self {
        if ($this->isNull()) {
            return new Vector2;
        }
        $r = clone $this;
        $v = pow($r->x, 2) + pow($r->y, 2)
            |> sqrt(...);
        $r->x = $r->x / $v;
        $r->y = $r->y / $v;
        return $r;
    }

    public function normalizeInPlace(): self {
        if ($this->isNull()) {
            return $this;
        }
        $v = pow($this->x, 2) + pow($this->y, 2)
            |> sqrt(...);
        $this->x = $this->x / $v;
        $this->y = $this->y / $v;
        return $this;
    }

    /** Checks if the current vector is a null vector */
    public function isNull(): bool {
        // NOTE: might be some floating point precision issues, once we go subpixel
        return $this->x === 0.0 && $this->y === 0.0;
    }
}

/**
* creates a vector2 as a Raylib C structure, rather than a Php object
*/
function makec_vector2(float $x = 0.0, float $y = 0.0): CData {
    $c = RL_FFI->new('struct Vector2');
    $c->x = $x;
    $c->y = $y;
    return $c;
}

?>
