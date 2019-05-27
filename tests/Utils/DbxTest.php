<?php
use PHPUnit\Framework\TestCase;
use Burdock\Utils\Dbx;

class DbxTest extends TestCase
{
    private $app_key;
    private $app_secret;
    private $access_token;
    private $base_dir;
    private $dbx;
    private $pathToDbxFile;

    public function setUp():void
    {
        $dotenv = Dotenv\Dotenv::create(__DIR__. '/../');
        $dotenv->load();
        $this->app_key = getenv('DBX_APP_KEY');
        $this->secret_key = getenv('DBX_APP_SECRET');
        $this->access_token = getenv('DBX_ACCESS_TOKEN');
        $this->base_dir = getenv('DBX_BASE_DIR');
        $this->dbx = new Dbx($this->app_key, $this->secret_key, $this->access_token, $this->base_dir);
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
        $now = date('YmdHis');
        $pathToLocalFile = __DIR__ . '/dropbox_test_local.txt';
        $this->pathToDbxFile = 'dropbox_test_' . $now . '.txt';
        return $this->dbx->upload($pathToLocalFile, $this->pathToDbxFile);
    }

    public function test_fileUpload_and_checkFileExists()
    {
        $uploaded = $this->uploadFile();
        $this->assertEquals($this->pathToDbxFile, $uploaded->getName());
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
    public function test_rotate_uploadedFile()
    {
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $uploaded = $this->uploadFile();
        $deleted = $this->dbx->rotate(2, null, true);
        $this->assertEquals(3, count($deleted));
        $files = $this->dbx->listFolder();
        foreach($files as $file) {
            $this->dbx->delete($file);
        }
    }
}