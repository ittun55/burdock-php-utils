<?php
use Burdock\StorageAdapter\DropboxAdapter;
use Burdock\Utils\Str;
use Burdock\Utils\Fs;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;

const DS = DIRECTORY_SEPARATOR;

class DropboxAdapterTest extends TestCase
{
    protected $logger  = null;
    protected $adapter = null;

    public function setUp(): void
    {
        $this->logger = new Logger('DbxTest');
        $handler = new StreamHandler('php://stdout', Logger::INFO);
        $this->logger->pushHandler($handler);
        $env = Dotenv\Dotenv::create(__DIR__. '/../');
        $env->load();
        $app_key = getenv('DBX_APP_KEY');
        $secret_key = getenv('DBX_APP_SECRET');
        $access_token = getenv('DBX_ACCESS_TOKEN');
        $base_dir = getenv('DBX_BASE_DIR');
        $this->adapter = new DropboxAdapter(
            $app_key, $secret_key,
            $access_token, $base_dir, $this->logger);
    }

    public function test_fileOperation()
    {
        $jpg   = 'sky_mountain_sunflower.jpg';
        $l_jpg = __DIR__ . DS . $jpg;
        $r_jpg = Str::randomChars(8, ['/','\\',':']) . '/' . $jpg;
        $this->adapter->saveFile($l_jpg, $r_jpg);

        $xls   = 'sample_template.xlsx';
        $l_xls = __DIR__ . DS . $xls;
        $r_xls = Str::randomChars(8, ['/','\\',':']) . '/' . $xls;
        $this->adapter->saveFile($l_xls, $r_xls);


        $d_jpg = $this->adapter->getFile($r_jpg, __DIR__.DS.'..'.DS.'tmp');
        $original = new \SplFileObject($l_jpg);
        $download = new \SplFileObject($d_jpg);
        $this->assertEquals($original->getSize(), $download->getSize());

        $d_xls = $this->adapter->getFile($r_xls, __DIR__.DS.'..'.DS.'tmp');
        $original = new \SplFileObject($l_xls);
        $download = new \SplFileObject($d_xls);
        $this->assertEquals($original->getSize(), $download->getSize());
        sleep(2);
        $list = $this->adapter->getList('/');
        sleep(2);
        $this->adapter->deleteRecursive($list);
        Fs::rmDir(realpath(__DIR__.DS.'..'.DS.'tmp'));
        //Fs::rmDir($d_xls);
    }
}
