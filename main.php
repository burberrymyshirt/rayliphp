#!/usr/bin/env php
<?php

require_once './Raylib.php';
define('RAYLIB_FFI', Raylib::getFFI());
require_once './Color.php';
require_once './utils.php';

define('WIDTH', 800);
define('HEIGHT', 600);
define('TARGET_FPS', 60);
define('WINDOW_TITLE', "din mor er grim");

function main(): int
{
    RAYLIB_FFI->InitWindow(WIDTH, HEIGHT, WINDOW_TITLE);
    RAYLIB_FFI->SetTargetFPS(TARGET_FPS);
    while (! RAYLIB_FFI->WindowShouldClose()) {
        draw_screen();
        echo (RAYLIB_FFI->GetFPS().PHP_EOL);
    }
    return 0;
}

function draw_screen(): void {
    RAYLIB_FFI->BeginDrawing();
    RAYLIB_FFI->ClearBackground(LIGHTGRAY);
    RAYLIB_FFI->DrawRectangle(WIDTH/2, HEIGHT/2, 2, 2, WHITE);
    RAYLIB_FFI->EndDrawing();
}

exit(main());
?>
