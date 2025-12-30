#!/usr/bin/env php
<?php

enum Colors: string {
    case LIGHTGRAY = "200,200,200,255";   // Light Gray
    case GRAY = "130,130,130,255";        // Gray
    case DARKGRAY = "80,80,80,255";       // Dark Gray
    case YELLOW = "253,249,0,255";        // Yellow
    case GOLD = "255,203,0,255";          // Gold
    case ORANGE = "255,161,0,255";        // Orange
    case PINK = "255,109,194,255";        // Pink
    case RED = "230,41,55,255";           // Red
    case MAROON = "190,33,55,255";        // Maroon
    case GREEN = "0,228,48,255";          // Green
    case LIME = "0,158,47,255";           // Lime
    case DARKGREEN = "0,117,44,255";      // Dark Green
    case SKYBLUE = "102,191,255,255";     // Sky Blue
    case BLUE = "0,121,241,255";          // Blue
    case DARKBLUE = "0,82,172,255";       // Dark Blue
    case PURPLE = "200,122,255,255";      // Purple
    case VIOLET = "135,60,190,255";       // Violet
    case DARKPURPLE = "112,31,126,255";   // Dark Purple
    case BEIGE = "211,176,131,255";       // Beige
    case BROWN = "127,106,79,255";        // Brown
    case DARKBROWN = "76,63,47,255";      // Dark Brown
    case WHITE = "255,255,255,255";       // White
    case BLACK = "0,0,0,255";             // Black
    case BLANK = "0,0,0,0";               // Blank (Transparent)
    case MAGENTA = "255,0,255,255";       // Magenta
    case RAYWHITE = "245,245,245,255";    // My own White (raylib logo)

    public function toC(\FFI &$rl): \FFI\CData {
        $color = \array_combine(['r', 'g', 'b', 'a'], \array_map('intval',\explode(',', $this->value)));
        $return = $rl->new('struct Color');
        foreach ($color as $k => $v) {
            $return->$k = $v;
        }
        return $return;
    }
}

function main()
{
    $rl = getRaylib();
    $rl->InitWindow(800, 600, "din mor er grim");
    while (! $rl->WindowShouldClose()) {
        $rl->BeginDrawing();
        $rl->ClearBackground(Colors::RED->toC($rl));
        echo uniqid().PHP_EOL;
        $rl->EndDrawing();
    }
    return 0;
}

function getRaylib(): \FFI {
    return \FFI::cdef(
        "void InitWindow(int width, int height, const char *title);
        void BeginDrawing();
        void EndDrawing();
        bool WindowShouldClose();
        typedef struct Color {
            unsigned char r;        // Color red value
            unsigned char g;        // Color green value
            unsigned char b;        // Color blue value
            unsigned char a;        // Color alpha value
        } Color;
        void ClearBackground(Color color);
        ",
        __DIR__."/raylib-5.5_linux_amd64/lib/libraylib.so.5.5.0");
}

main();
