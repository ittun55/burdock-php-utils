<?php
use PHPUnit\Framework\TestCase;
use Burdock\Utils\Fs;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class FsTest extends TestCase
{
    private $logger;

    public function setUp(): void
    {
        $this->logger = new Logger('FsTest');
        $handler = new StreamHandler('php://stdout', Logger::INFO);
        $this->logger->pushHandler($handler);
    }

    public function test_makeTempDir(): void
    {
        $base_dir = __DIR__;
        $tmp_dir = Fs::makeTempDir($base_dir);
        $this->assertTrue(file_exists($tmp_dir));
        Fs::rmDir($tmp_dir);
    }

    public function test_rmDir(): void
    {
        $base_dir = __DIR__;
        $tmp1_dir = Fs::makeTempDir($base_dir);
        $tmp2_dir = Fs::makeTempDir($tmp1_dir);
        $this->assertTrue(file_exists($tmp2_dir));
        Fs::rmDir($tmp1_dir);
        $this->assertFalse(file_exists($tmp1_dir));
    }
}