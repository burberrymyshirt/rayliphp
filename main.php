#!/usr/bin/env php
<?php

declare(strict_types=1);

// init raylib FFI and utils before anything else.
// utils should always be independeant of other require-statements,
// otherwise those functions should be defined in the actual files.
require_once './utils.php';
require_once './Raylib.php';

// These are defined as they might be used in other classes below,
// e.g. to convert them to C structs. It does kind of suck though.
define('RL_FFI', Raylib::getFFI());
define('RL_WRAPPER', Raylib::class);

require_once './Color.php';
require_once './Vector2.php';
// require_once './Vector3.php'; //TODO: implement

define('WIDTH', 1200);
define('HEIGHT', 900);
define('TARGET_FPS', 60);
define('WINDOW_TITLE', "din mor er grim");

// TODO: we keep everything in the php wrapper for now,
// but consider going full C style with it. I don't know which approach is faster.
// define('DIR_UP', makec_vector2(0, 1));
// define('DIR_DOWN', makec_vector2(0, -1));
// define('DIR_LEFT', makec_vector2(-1, 0));
// define('DIR_RIGHT', makec_vector2(1, 0));
define('DIR_UP', new Vector2(0, -1));
define('DIR_DOWN', new Vector2(0, 1));
define('DIR_LEFT', new Vector2(-1, 0));
define('DIR_RIGHT', new Vector2(1, 0));

function main(): int
{
    // game init
    $state = GameState::init();

    // raylib init (ffi is already initialized in the defines above)
    RL_FFI->InitWindow(WIDTH, HEIGHT, WINDOW_TITLE);
    RL_FFI->SetTargetFPS(TARGET_FPS);

    // event loop
    while (! RL_FFI->WindowShouldClose()) {
        handle_keypress($state);
        draw_screen($state);
    }

    // cleanup
    RL_FFI->CloseWindow();
    return 0;
}

function handle_keypress(GameState &$state): void {
    // TODO: scale by time and not the fps of raylib
    $speed = 5;
    $keys_pressed = [];
    if (Raylib::isKeyDown(KeyboardKey::KEY_UP)) {
        $keys_pressed[] = DIR_UP;
    }
    if (Raylib::isKeyDown(KeyboardKey::KEY_DOWN)) {
        $keys_pressed[] = DIR_DOWN;
    }
    if (Raylib::isKeyDown(KeyboardKey::KEY_RIGHT)) {
        $keys_pressed[] = DIR_RIGHT;
    }
    if (Raylib::isKeyDown(KeyboardKey::KEY_LEFT)) {
        $keys_pressed[] = DIR_LEFT;
    }
    $direction = new Vector2();
    foreach ($keys_pressed as $key_pressed) {
        $direction->addInPlace($key_pressed);
    }
    if (! $direction->isNull()) {
        $state->circle->position->addInPlace($direction->normalize()->scale($speed));
    }
}

function draw_screen(GameState &$state): void {
    RL_FFI->BeginDrawing();
    RL_FFI->ClearBackground(RAYWHITE);
    RL_FFI->DrawCircleV($state->circle->position->toC(), 69, BLACK);
    RL_FFI->EndDrawing();
}


class GameState {
    public function __construct(
        public Circle $circle,
    ) {}

    public static function init(): self {
        return new self(new Circle(new Vector2(WIDTH/2, HEIGHT/2), 69));
    }
}

class Circle {
    public function __construct(
        public Vector2 $position,
        public int $diameter,
    ) {}
}

exit(main());
?>
