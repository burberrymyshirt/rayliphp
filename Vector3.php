<?php

declare(strict_types=1);

use \FFI\CData;

// TODO: This should probably just be some sort of static class modifying
// raylib vectors, as it is probably pretty expensive to keep calling the toC()
// function to use as a parameter to all the vector functions.
// It is more idiomatic php code, but that is not necessarily what wee are going for.
//
// I don't even think it matters that much, this file has just become way too messy way too quickly
class Vector3 {
    public function __construct(
        public float $x = 0.0,
        public float $y = 0.0,
        public float $z = 0.0,
    ) {}

    public function add(self $in): self {
        return new self($this->x + $in->x, $this->y + $in->y, $this->z + $in->z);
    }

    public function scale(float $factor): self {
        return new self($this->x * $factor, $this->y * $factor, $this->y * $factor);
    }

    public function mul(self $in): self {
        return new self($this->x * $in->x, $this->y * $in->y, $this->z * $in->z);
    }

    public function normalize(): self {
        if ($this->isNull()) {
            return new Vector3;
        }
        $v = [pow($this->x, 2), pow($this->y, 2), pow($this->z, 2)]
            |> array_sum(...)
            |> sqrt(...);
        return new self($this->x / $v, $this->y / $v, $this->z / $v);
    }

    /** Checks if the current vector is a null vector */
    public function isNull(): bool {
        // NOTE: might be some floating point precision issues, once we go subpixel
        return $this->x === 0.0 && $this->y === 0.0 && $this->z === 0.0;
    }
}

/**
* creates a vector2 as a Raylib C structure, rather than a Php object
*/
function vector3_makec(float $x = 0.0, float $y = 0.0, float $z = 0.0): CData {
    $c = RL_FFI->new('struct Vector3');
    $c->x = $x;
    $c->y = $y;
    $c->z = $z;
    return $c;
}

function vector3_toc(Vector3 $vector): \FFI\CData {
    $c = RL_FFI->new('struct Vector3');
    $c->x = $vector->x;
    $c->y = $vector->y;
    $c->z = $vector->z;
    return $c;
}

?>
