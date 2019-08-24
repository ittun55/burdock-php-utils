<?php
namespace Burdock\StorageAdapter;

use Psr\Log\LoggerInterface;

class DropboxConfig
{
    public $app_key;
    public $secret;
    public $token;
    public $base_dir;
    public $logger;

    public function __construct(string $env_path)
    {
        $env = \Dotenv\Dotenv::create($env_path);
        $env->load();
        $this->app_key  = getenv('DBX_APP_KEY');
        $this->secret   = getenv('DBX_APP_SECRET');
        $this->token    = getenv('DBX_ACCESS_TOKEN');
        $this->base_dir = getenv('DBX_BASE_DIR');
    }
}