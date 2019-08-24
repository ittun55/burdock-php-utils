<?php
namespace Burdock\StorageAdapter;

use Burdock\Utils\Fs;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;
use Kunnu\Dropbox\Models\FileMetadata;
use Kunnu\Dropbox\Models\FolderMetadata;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

const DS = DIRECTORY_SEPARATOR;

/**
 * Class DropboxAdapter
 *
 * https://github.com/kunalvarma05/dropbox-php-sdk/wiki/Upload-and-Download-Files
 * https://kunalvarma05.github.io/dropbox-php-sdk/master/Kunnu/Dropbox/Dropbox.html
 *
 * @package Burdock\StorageAdapter
 */
class DropboxAdapter implements IStorageAdapter
{
    protected $dropbox;
    /**
     * Dropbox 上のルートパス
     *
     * 誤操作でこのパス以外のファイルを触らないために設定
     *
     * @var array|false|string
     */
    protected $base_dir;
    protected $logger;

    public function __construct($app_key, $secret, $token, $base_dir, ?LoggerInterface $logger=null)
    {
        if (!$app_key || !$secret || !$token || !$base_dir)
            throw new \InvalidArgumentException('app_key, secret and token for Dropbox is required.');
        $this->base_dir = $base_dir;
        $app = new DropboxApp($app_key, $secret, $token);
        $this->dropbox  = new Dropbox($app);
        $this->logger   = is_null($logger) ? new NullLogger() : $logger;
    }

    public function getDbxFullPath(string $remote=null): string
    {
        $_path = $this->base_dir;
        if (!is_null($remote))
            $_path.= (substr($remote, 0, 1) == '/') ? $remote : '/' . $remote;
        return $_path;
    }

    public function getList(string $remote, int $depth = 0): array
    {
        $_path = ($depth === 0) ? $this->getDbxFullPath($remote) : $remote;
        $listFolderContents = $this->dropbox->listFolder($_path);
        $items = [];
        foreach ($listFolderContents->getItems() as $content) {
            if ($content instanceof FolderMetadata) {
                $_skipPrefix = true;
                $folder = $content->getPathDisplay();
                $items[] = [
                    'title'    => $content->getName(),
                    'path'     => $folder,
                    'children' => $this->getList($folder, $depth + 1)
                ];
            } else {
                $items[] = [
                    'title' => $content->getName(),
                    'path'  => $content->getPathDisplay(),
                    'modified_at' => $content->getServerModified()
                ];
            }
        }
        return $items;
    }

    public function getFile(string $remote, string $local): string
    {
        $_path = $this->getDbxFullPath($remote);
        $file = $this->dropbox->download($_path);
        $meta = $file->getMetadata();
        $local_path = Fs::makeTempDir($local) . DS . $meta->getName();
        file_put_contents($local_path, $file->getContents());
        unset($file);
        return $local_path;
    }

    public function saveFile(string $local, string $remote): bool
    {
        $dropboxFile = new DropboxFile($local);
        $this->logger->info('start uploading from: ' . $local . ' to: ' . $this->getDbxFullPath($remote));
        $fileMeta = $this->dropbox->upload($dropboxFile, $this->getDbxFullPath($remote), ['autorename' => true]);
        return $fileMeta->getId() ? true : false;
    }

    public function delete(string $remote, int $depth = 0): bool
    {
        $_path = ($depth === 0) ? $this->getDbxFullPath($remote) : $remote;
        $metadata = $this->dropbox->delete($_path);
        return ($metadata instanceof FileMetadata || $metadata instanceof FolderMetadata);
    }

    public function deleteRecursive($items, int $depth = 0): bool
    {
        foreach ($items as $item) {
            if (array_key_exists('children', $item)
                && !$this->deleteRecursive($item['children'], $depth + 1))
            {
                return false;
            }
            if (!$this->delete($item['path'], $depth + 1)) {
                return false;
            }
        }
        return true;
    }

    public function createFolder(string $remote, int $depth = 0): bool
    {
        $_path = ($depth === 0) ? $this->getDbxFullPath($remote) : $remote;
        $metadata = $this->dropbox->createFolder($_path);
        return ($metadata instanceof FolderMetadata);
    }
}