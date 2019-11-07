<?php

namespace Burdock\Utils;

const DS = DIRECTORY_SEPARATOR;

class Fs
{
    public static function makeTempDir(string $base_dir): string
    {
        $rand = Str::randomChars(8, ['#','$','&']);
        $dir = $base_dir . DS . $rand;
        mkdir($dir, 0700, true);
        return $dir;
    }

    public static function rmDir(string $path): bool
    {
        if (is_dir($path)) {
            $items = scandir($path);
            foreach ($items as $item) {
                if (in_array($item, ['.', '..'])) continue;
                $target = $path . DS . $item;
                if (is_dir($target)) {
                    self::rmDir($target);
                } else {
                    unlink($target);
                }
            }
            return rmdir($path);
        } else {
            return false;
        }
    }
}