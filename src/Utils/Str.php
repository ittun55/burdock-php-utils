<?php
namespace Burdock\Utils;

use Burdock\Chainable\Chainable;
use Closure;

class Str
{
    public static function populateChars(): callable
    {
        return function() {
            return array_merge(
                range('a', 'z'),
                range('0', '9'),
                range('A', 'Z'),
                ['!','@','$','&','#','-','_','+']
            );
        };
    }

    public static function excludeChars(array $excludes): callable
    {
        return function ($population) use ($excludes): array {
            return array_values(array_diff($population, $excludes));
        };
    }

    public static function randomize(int $length=8): callable
    {
        return function(array $population) use ($length) {
            return array_map(function($i) use($population) {
                return $population[rand(0, count($population) - 1)];
            }, range(0, $length-1));
        };
    }

    public static function randomChars(int $length, array $excludes=[]): string
    {
        $chain = (new Chainable())
          ->process('populate characters', Str::populateChars())
          ->process('exclude some characters', Str::excludeChars($excludes))
          ->process('randomize characters', Str::randomize($length));
        return implode($chain->getValue());
    }

    public static function startsWith(string $chars, string $sentence) : bool
    {
        return $chars === substr($sentence, 0,  strlen($chars));
    }

    public static function endsWith(string $chars, string $sentence) : bool
    {
        return $chars === substr($sentence, -1 * strlen($chars));
    }
}
