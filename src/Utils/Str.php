<?php
namespace Burdock\Utils;

use Burdock\Utils\Job\Chain;
use Burdock\Utils\Job\NamedJob;

class Str
{
    public static function randomPassword($length, array $excludes=[])
    {
        $generatePopulationChars = new NamedJob('generatePopulationChars', function($value) {
            return array_merge(
                range('a', 'z'),
                range('0', '9'),
                range('A', 'Z'),
                ['!','@','$','&','#','-','_','+']
            );
        });

        $excludeChars = new NamedJob('excludeChars', function($population, $excludes) {
            return array_values(array_diff($population, $excludes));
        });

        $randomChars = new NamedJob('randomChars', function($population, $length) {
            return array_map(function($i) use($population) {
                return $population[rand(0, count($population) - 1)];
            }, range(0, $length-1));
        });

        $chain = (new Chain())
          ->process($generatePopulationChars)
          ->process($excludeChars, $excludes)
          ->process($randomChars, $length);

        return implode($chain->getValue());
    }
}
