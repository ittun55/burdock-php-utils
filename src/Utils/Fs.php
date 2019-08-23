<?php

namespace Burdock\Utils;

use DirectoryIterator;
const DS = DIRECTORY_SEPARATOR;

class Fs
{
    public static function makeTempDir(string $base_dir): string
    {
        $rand = Str::randomChars(8, ['#','$','&']);
        $dir = $base_dir . DS . $rand;
        mkdir($dir, '0600', true);
        return $dir;
    }

    public static function rmDir(string $path): bool
    {
        $dir = new DirectoryIterator($path);
        if (!$dir->isDir()) { return false; }
        foreach ($dir as $e) {
            if ($e->isDot()) continue;
            if ($e->isDir()) {
                if (!self::rmDir($e->getPathName()))
                    return false;
            } else {
                unlink($e->getPathName()) ;
            }
        }
        return rmdir($path);
    }
}