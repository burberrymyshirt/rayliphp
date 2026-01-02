<?php

//default predefined raylib colors
define('LIGHTGRAY', Color::makeC([200, 200, 200, 255]));
define('GRAY', Color::makeC([130, 130, 130, 255]));
define('DARKGRAY', Color::makeC([80, 80, 80, 255]));
define('YELLOW', Color::makeC([253, 249, 0, 255]));
define('GOLD', Color::makeC([255, 203, 0, 255]));
define('ORANGE', Color::makeC([255, 161, 0, 255]));
define('PINK', Color::makeC([255, 109, 194, 255]));
define('RED', Color::makeC([230, 41, 55, 255]));
define('MAROON', Color::makeC([190, 33, 55, 255]));
define('GREEN', Color::makeC([0, 228, 48, 255]));
define('LIME', Color::makeC([0, 158, 47, 255]));
define('DARKGREEN', Color::makeC([0, 117, 44, 255]));
define('SKYBLUE', Color::makeC([102, 191, 255, 255]));
define('BLUE', Color::makeC([0, 121, 241, 255]));
define('DARKBLUE', Color::makeC([0, 82, 172, 255]));
define('PURPLE', Color::makeC([200, 122, 255, 255]));
define('VIOLET', Color::makeC([135, 60, 190, 255]));
define('DARKPURPLE', Color::makeC([112, 31, 126, 255]));
define('BEIGE', Color::makeC([211, 176, 131, 255]));
define('BROWN', Color::makeC([127, 106, 79, 255]));
define('DARKBROWN', Color::makeC([76, 63, 47, 255]));
define('WHITE', Color::makeC([255, 255, 255, 255]));
define('BLACK', Color::makeC([0, 0, 0, 255]));
define('BLANK', Color::makeC([0, 0, 0, 0]));
define('MAGENTA', Color::makeC([255, 0, 255, 255]));
define('RAYWHITE', Color::makeC([245, 245, 245, 255]));

class Color {
    public function __construct(
        protected int $r,
        protected int $g,
        protected int $b,
        protected int $a,
    ) {}

    public function toC(): \FFI\CData {
        $c = Raylib::getFFI()->new('struct Color');
        \FFI::memcpy($c, pack('C4', ...array_values(get_object_vars($this))), 4);
        return $c;
    }

    public static function makeC(array $rgba): \FFI\CData {
        return new self(...$rgba)->toC();
    }
}
?>
