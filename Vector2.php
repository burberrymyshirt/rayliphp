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

    public function add(self $in): self {
        return new self($this->x + $in->x, $this->y + $in->y);
    }

    public function scale(float $factor): self {
        return new self($this->x * $factor, $this->y * $factor);
    }

    public function mul(self $in): self {
        return new self($this->x * $in->x, $this->y * $in->y);
    }

    public function normalize(): self {
        // maybe do an assert here, and make the user handle the case as to not hide bugs?
        if ($this->isZero()) {
            return new Vector2;
        }
        $v = pow($this->x, 2) + pow($this->y, 2)
            |> sqrt(...);
        return new self($this->x / $v, $this->y / $v);
    }

    public function cross(self $in): float {
        return ($this->x * $in->y) - ($this->y * $in->x);
    }

    public function dot(self $in): float {
        return ($this->x * $in->x) + ($this->y * $in->y);
    }

    /** Checks if the current vector is a zero vector */
    public function isZero(): bool {
        // NOTE: might be some floating point precision issues, once we go subpixel
        return $this->x === 0.0 && $this->y === 0.0;
    }
}

/**
* creates a vector2 as a Raylib C structure, rather than a Php object
*/
function vector2_makec(float $x = 0.0, float $y = 0.0): CData {
    $c = RL_FFI->new('struct Vector2');
    $c->x = $x;
    $c->y = $y;
    return $c;
}

function vector2_toc(Vector2 $vector): CData {
    $c = RL_FFI->new('struct Vector2');
    $c->x = $vector->x;
    $c->y = $vector->y;
    return $c;
}

?>
