<?php
use PHPUnit\Framework\TestCase;
use Burdock\Utils\Str;

class StrTest extends TestCase
{
    public function test_passwordLength()
    {
        $generated = Str::randomChars(3);
        $this->assertEquals(3, mb_strlen($generated));
        $generated = Str::randomChars(5);
        $this->assertEquals(5, mb_strlen($generated));
    }

    public function test_excludeChars()
    {
        $excludes = array_merge(
            range('a', 'z'),
            range('1', '9'),
            range('A', 'Z'),
            ['!','@','$','&','#','-','_','+']
        );
        $generated = Str::randomChars(3, $excludes);
        $this->assertEquals('000', $generated);

        $excludes = array_merge(
            range('b', 'z'),
            range('0', '9'),
            range('A', 'Z'),
            ['!','@','$','&','#','-','_','+']
        );
        $generated = Str::randomChars(3, $excludes);
        $this->assertEquals('aaa', $generated);
    }

    public function test_endsWith()
    {
        $sentence = 'abc_xyz';
        $end1   = 'xyz';
        $this->assertTrue(Str::endsWith($end1, $sentence));
        $end2   = '.xyz';
        $this->assertFalse(Str::endsWith($end2, $sentence));
        $end3   = '_xyz';
        $this->assertTrue(Str::endsWith($end3, $sentence));
    }
}