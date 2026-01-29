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
        draw_screen($state);
        // draw_screen($state);
    }

    // cleanup
    RL_FFI->CloseWindow();
    return 0;
}

function draw_screen(GameState &$state): void {
    // TODO: we need to unify the scaling in here. Right now tiles are streteched,
    //  and the player is a static size. They should be drawn in the same way, as
    //  it would probably be ideal in case we want to change the resolution later.
    RL_FFI->BeginDrawing();
    {
        RL_FFI->ClearBackground(RAYWHITE);
        $level = $state->level;
        // draw level
        foreach ($level->tiles as $row_num => $row) {
            foreach ($row as $col_num => $col) {
                $function = match ($col) {
                    Level::TILE_TYPE_WALL => 'DrawRectangle',
                    Level::TILE_TYPE_EMPTY => 'DrawRectangleLines',
                    default => throw new RuntimeException('encountered unhandled level type'),
                };
                // This only works for this kind of draw functions 🙂
                call_user_func([RL_FFI, $function],
                    $row_num*$level->tileWidth(),
                    $col_num*$level->tileHeight(),
                    $level->tileWidth(),
                    $level->tileHeight(),
                    BLACK,
                );
            }
        }

        RL_FFI->DrawRectangle(
            $state->me->position->x,
            $state->me->position->y,
            $state->me->size,
            $state->me->size,
            BLUE,
        );
    }
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
    $level = $state->level;

    $tile_width = $level->tileWidth();
    $tile_height = $level->tileHeight();

    $new_pos = $pending_movement->add($player_pos);
    $corners = $state->me->getCorners($new_pos);
    foreach ($corners as $corner) {
        ['x'=>$x, 'y'=>$y] = $corner;
        $tile_x = (int)($x / $tile_width);
        $tile_y = (int)($y / $tile_height);

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

    public function getCorners(Vector2 $position): array {
        $player_size = $this->size;
        return [
            'top_left' => ['x' => $position->x, 'y' => $position->y],
            'top_right' => ['x' => $position->x + $player_size, 'y' => $position->y],
            'bottom_left' => ['x' => $position->x, 'y' => $position->y + $player_size],
            'bottom_right' => ['x' => $position->x + $player_size, 'y' => $position->y + $player_size],
        ];
    }
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
