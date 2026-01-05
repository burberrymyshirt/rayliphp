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
require_once './Vector3.php';

define('WIDTH', 1200);
define('HEIGHT', 900);
define('TARGET_FPS', 60);
define('WINDOW_TITLE', 'din mor er grim');

// TODO: we keep everything in the php wrapper for now,
// but consider going full C style with it. I don't know which approach is faster.
// define('DIR_UP', vector2_makec(0, 1));
// define('DIR_DOWN', vector2_makec(0, -1));
// define('DIR_LEFT', vector2_makec(-1, 0));
// define('DIR_RIGHT', vector2_makec(1, 0));
define('DIR_UP', new Vector2(0, -1));
define('DIR_DOWN', new Vector2(0, 1));
define('DIR_LEFT', new Vector2(-1, 0));
define('DIR_RIGHT', new Vector2(1, 0));

define('DIR_FORWARD', new Vector2(0, -1));
define('DIR_BACKWARD', new Vector2(0, 1));

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
        draw_minimap($state);
        // draw_screen($state);
    }

    // cleanup
    RL_FFI->CloseWindow();
    return 0;
}

function draw_minimap(GameState &$state): void {
    // $state->me->camera->direction
    RL_FFI->BeginDrawing();
    RL_FFI->ClearBackground(RAYWHITE);
    $level = $state->level;
    // draw level
    foreach ($level->tiles as $row_num => $row) {
        foreach ($row as $col_num => $col) {
            if ($col === 1) {
                RL_FFI->DrawRectangle($row_num*$level->tileWidth(), $col_num*$level->tileHeight(), $level->tileWidth(), $level->tileHeight(), BLACK);
            } else {
                RL_FFI->DrawRectangleLines($row_num*$level->tileWidth(), $level->tileHeight(), $level->tileWidth(), $level->tileHeight(), BLACK);
            }
        }
    }
    $size = 10;
    RL_FFI->DrawRectangle($state->me->position->x - $size/2, $state->me->position->y - $size/2, $size, $size, BLUE);
    RL_FFI->EndDrawing();
}

function handle_keypress(GameState &$state): void {
    // handle movement
    {
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
        $direction = new Vector2;
        // TODO: add movement for 3d camera instead
        foreach ($keys_pressed as $key_pressed) {
            $direction = $direction->add($key_pressed);
        }
        $state->me->position = $state->me->position->add($direction);
    }
}

function draw_screen(GameState &$state): void {
    RL_FFI->BeginDrawing();
    RL_FFI->ClearBackground(RAYWHITE);

    $sections = $state->sections;
    foreach ($sections as $section) {
        if ($state->me->camera->canSee($section)) {
            $section;
        }
    }

    RL_FFI->EndDrawing();
}

class GameState {
    public function __construct(
        public Player $me,
        /** @var array<Section> */
        public Level $level,
    ) {}

    // public static function init(): self {
    //     $level = Level::init();
    //     return new self(
    //         new Player(
    //             new Camera(DIR_FORWARD, 1),
    //         ),
    //         $level,
    //     );
    // }
    public static function init(): self {
        $player = new Player(new Camera(new Vector2, 1), new Vector2(WIDTH/2, HEIGHT/2));
        $map = Level::init();
        return new self(
            $player,
            $map,
        );
    }
}

class Player {
    public function __construct(
        public Camera $camera,
        public Vector2 $position,
    ) {}
}

class Level {
    public function __construct(
        /** @var array<array<int>> */
        public array $tiles,
    ) {}

    public static function init(): self {
        return new self([
            [1,1,1,1,1,1,1,1,1,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,0,0,0,0,0,0,0,0,1],
            [1,1,1,1,1,1,1,1,1,1],
        ]);
    }

    public function height(): int {
        return count(array_first($this->tiles));
    }

    public function width(): int {
        return count($this->tiles);
    }

    public function tileHeight(): int {
        return $this->height()/10;
    }

    public function tileWidth(): int {
        return $this->width()/10;
    }
}

class Camera {
    public function __construct(
        public Vector2 $direction,
        public int $fov,
    ) {}

    public function canSee(Section $section) {
        // shoot ray
        $fov = $this->fov;
    }
}

exit(main());
?>
