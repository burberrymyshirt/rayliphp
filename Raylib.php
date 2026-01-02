<?php

class Raylib {
    protected static \FFI $ffi;

    public static function getFFI(): \FFI
    {
        return isset(self::$ffi)
            ? self::$ffi
            : self::$ffi = \FFI::cdef(<<<RAYLIB
                void InitWindow(int width, int height, const char *title);
                void BeginDrawing();
                void EndDrawing();
                bool WindowShouldClose();
                typedef struct Color {
                    unsigned char r;        // Color red value
                    unsigned char g;        // Color green value
                    unsigned char b;        // Color blue value
                    unsigned char a;        // Color alpha value
                } Color;
                typedef struct Vector2 {
                    float x;
                    float y;
                } Vector2;
                void ClearBackground(Color color);
                void SetTargetFPS(int fps);
                float GetFrameTime(void);
                int GetFPS(void);
                void DrawRectangle(int posX, int posY, int width, int height, Color color);
                void DrawPixel(int posX, int posY, Color color);
                void DrawPixelV(Vector2 vector, Color color);
                RAYLIB,
                __DIR__."/raylib-5.5_linux_amd64/lib/libraylib.so.5.5.0");
    }
}
?>
