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
        update_state($state);
        draw_minimap($state);
        // draw_screen($state);
    }

    // cleanup
    RL_FFI->CloseWindow();
    return 0;
}

function draw_minimap(GameState &$state): void {
    RL_FFI->BeginDrawing();
    RL_FFI->ClearBackground(RAYWHITE);
    $level = $state->level;
    // draw level
    foreach ($level->tiles as $row_num => $row) {
        foreach ($row as $col_num => $col) {
            if ($col === Level::TILE_TYPE_WALL) {
                RL_FFI->DrawRectangle(
                    $row_num*$level->tileWidth(),
                    $col_num*$level->tileHeight(),
                    $level->tileWidth(),
                    $level->tileHeight(),
                    BLACK,
                );
            } else {
                RL_FFI->DrawRectangleLines(
                    $row_num*$level->tileWidth(),
                    $col_num*$level->tileHeight(),
                    $level->tileWidth(),
                    $level->tileHeight(),
                    BLACK,
                );
            }
        }
    }

    $player_width = player_width($state);
    $player_height = player_height($state);
    RL_FFI->DrawRectangle(
        $state->me->position->x,
        $state->me->position->y,
        $player_width,
        $player_height,
        BLUE,
    );
    RL_FFI->EndDrawing();
}

function update_state(GameState &$state): void {
    // handle movement
    {
        // TODO: scale by time and not the fps of raylib
        $player_speed = 5;
        $repel_speed = 0.25;
        $keys_pressed = [];
        if (Raylib::isKeyDown(KeyboardKey::KEY_UP)) {
            $keys_pressed[] = DIR_UP->scale($player_speed);
        }
        if (Raylib::isKeyDown(KeyboardKey::KEY_DOWN)) {
            $keys_pressed[] = DIR_DOWN->scale($player_speed);
        }
        if (Raylib::isKeyDown(KeyboardKey::KEY_RIGHT)) {
            $keys_pressed[] = DIR_RIGHT->scale($player_speed);
        }
        if (Raylib::isKeyDown(KeyboardKey::KEY_LEFT)) {
            $keys_pressed[] = DIR_LEFT->scale($player_speed);
        }
        $direction = new Vector2;
        // TODO: add movement for 3d camera instead
        foreach ($keys_pressed as $key_pressed) {
            $direction = $direction->add($key_pressed);
        }

        try {
            if (! will_player_collide_with_wall($state, $direction)) {
                $state->me->position = $state->me->position->add($direction);
            } else {
                $opposite_direction = $direction->scale(-1)->scale($repel_speed);
                $state->me->position = $state->me->position->add($opposite_direction);
            }
        } catch (OutOfBoundsException $ex) {
            dump($ex);
        }
    }
}

function will_player_collide_with_wall(GameState &$state, Vector2 $pending_movement): bool {
    $player_pos = $state->me->position;
    $player_size = $state->me->size;
    $level = $state->level;

    $tile_width = $level->tileWidth();
    $tile_height = $level->tileHeight();

    // $player_pos_y = (int)(($player_pos->y / $player_size) / $level->height());
    // $player_pos_x = (int)(($player_pos->x / $player_size) / $level->width());

    $player_width = player_width($state);
    $player_height = player_height($state);

    $player_pos_y = (int)($player_pos->y / $tile_width);
    $player_pos_x = (int)($player_pos->x / $tile_height);
    $new_pos = $pending_movement->add($player_pos);
    $corners = [
        ['x' => $new_pos->x, 'y' => $new_pos->y], // top-left
        ['x' => $new_pos->x + $player_width, 'y' => $new_pos->y], // top-right
        ['x' => $new_pos->x, 'y' => $new_pos->y + $player_height], // bottom-left
        ['x' => $new_pos->x + $player_width, 'y' => $new_pos->y + $player_height], // bottom-right
    ];
    foreach ($corners as $corner) {
        $tile_x = (int)($corner['x'] / $tile_width);
        $tile_y = (int)($corner['y'] / $tile_height);

        $tile = $level->getTile($tile_x, $tile_y);

        if (is_null($tile)) {
            throw new OutOfBoundsException('Tried to access an undefined tile when checking collision');
        }

        if (Level::TILE_TYPE_WALL === $tile) {
            return true;
        }
    }

    return false;
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
        public int $size = 10,
    ) {}
}

function player_height(GameState $state): int {
    return $state->level->tileHeight() / $state->me->size;
}

function player_width(GameState $state): int {
    return $state->level->tileWidth() / $state->me->size;
}

class Level {
    const TILE_TYPE_EMPTY = 0;
    const TILE_TYPE_WALL = 1;

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

    public function levelHeight(): int {
        return count(array_first($this->tiles));
    }

    public function levelWidth(): int {
        return count($this->tiles);
    }

    public function tileHeight(): int {
        return HEIGHT/$this->levelHeight();
    }

    public function tileWidth(): int {
        return WIDTH/$this->levelWidth();
    }

    /**
    * @return int The tile type of the given coordinates, and null if it doesn't exist
    */
    public function getTile(int $x, int $y): ?int {
        if (isset($this->tiles[$y][$x])) {
            return $this->tiles[$y][$x];
        }
        return null;
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
