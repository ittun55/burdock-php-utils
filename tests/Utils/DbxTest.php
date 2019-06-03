<?php
use PHPUnit\Framework\TestCase;
use Burdock\Utils\Dbx;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class DbxTest extends TestCase
{
    private $app_key;
    private $app_secret;
    private $access_token;
    private $base_dir;
    private $dbx;
    private $pathToDbxFile;
    private $logger;

    public function setUp():void
    {
        $this->logger = new Logger('DbxTest');
        $handler = new StreamHandler('php://stdout', Logger::INFO);
        $this->logger->pushHandler($handler);
        $dotenv = Dotenv\Dotenv::create(__DIR__. '/../');
        $dotenv->load();
        $this->app_key = getenv('DBX_APP_KEY');
        $this->secret_key = getenv('DBX_APP_SECRET');
        $this->access_token = getenv('DBX_ACCESS_TOKEN');
        $this->base_dir = getenv('DBX_BASE_DIR');
        $this->dbx = new Dbx(
            $this->app_key, $this->secret_key, 
            $this->access_token, $this->base_dir, $this->logger);
    }

    public function test_constructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Dbx(null, $this->secret_key, $this->access_token, $this->base_dir);
        $this->expectException(\InvalidArgumentException::class);
        new Dbx($this->app_key, null, $this->access_token, $this->base_dir);
        $this->expectException(\InvalidArgumentException::class);
        new Dbx($this->app_key, $this->secret_key, null, $this->base_dir);
        $this->expectException(\InvalidArgumentException::class);
        new Dbx($this->app_key, $this->secret_key, $this->access_token, null);
    }

    public function uploadFile()
    {
        sleep(1);
        $now = date('YmdHis');
        $pathToLocalFile = __DIR__ . '/dropbox_test_local.txt';
        $this->pathToDbxFile = 'test2/dropbox_test_' . $now . '.txt';
        return $this->dbx->upload($pathToLocalFile, $this->pathToDbxFile);
    }

    public function test_fileUpload_and_checkFileExists()
    {
        $uploaded = $this->uploadFile();
        $this->assertEquals($this->dbx->getBaseDir() . '/' . $this->pathToDbxFile, $uploaded->getPathDisplay());
    }

    /**
     * @test
     * @depends test_fileUpload_and_checkFileExists
     */
    public function test_delete_uploadedFile()
    {
        $this->expectException(Kunnu\Dropbox\Exceptions\DropboxClientException::class);
        $deleted = $this->dbx->delete($this->pathToDbxFile);
        $meta = $this->dbx->getMetadata($this->pathToDbxFile);
    }

    /**
     * @test
     * @depends test_delete_uploadedFile
     */    
    public function test_rotateByCount()
    {
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $deleted = $this->dbx->rotateByCount(2, 'test2/', true);
        $this->assertEquals(3, count($deleted));
    }

    /**
     * @test
     * @depends test_delete_uploadedFile
     */    
    public function test_rotateByDate()
    {
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $deleted = $this->dbx->rotateByDate(2, 'test2/', true);
        $this->assertEquals(0, count($deleted));
    }
}