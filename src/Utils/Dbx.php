<?php
namespace Burdock\Utils;

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

class Dbx
{
    protected $dropbox;
    protected $base_dir;

    public function __construct($app_key, $secret, $token, $base_dir)
    {
        if (!$app_key || !$secret || !$token || !$base_dir)
            throw new \InvalidArgumentException('All arguments are required for instantiate');
        $app = new DropboxApp($app_key, $secret, $token);
        $this->dropbox = new Dropbox($app);
        $this->base_dir = $base_dir;
    }

    public function getDbxFullPath($dbx_path)
    {
        $_path = $this->base_dir;
        if (!is_null($dbx_path))
            $_path.= (substr($dbx_path, 0, 1) == '/') ? $dbx_path : '/' . $dbx_path;
        return $_path;
    }

    public function getMetadata($dbx_path)
    {
        $path = $this->getDbxFullPath($dbx_path);
        return $this->dropbox->getMetadata($path);
    }

    public function download($dbx_path)
    {
        $path = $this->getDbxFullPath($dbx_path);
        $file = $this->dropbox->download($path);
        return $file->getContents();
    }

    public function upload($local_path, $dbx_path)
    {
        $mode = DropboxFile::MODE_READ;
        $dropboxFile = new DropboxFile($local_path, $mode);
        return $this->dropbox->upload($dropboxFile, $this->getDbxFullPath($dbx_path), ['autorename' => true]);
    }

    public function delete($dbx_path)
    {
        $path = $this->getDbxFullPath($dbx_path);
        return $this->dropbox->delete($path);
    }

    public function listFolder($dbx_path=null)
    {
        $_base_dir = $this->getDbxFullPath($dbx_path);
        $listFolderContents = $this->dropbox->listFolder($_base_dir);
        $items = [];
        foreach ($listFolderContents->getItems() as $content) {
            $items[] = $content->getName();
        }
        return $items;
    }

    public function rotate($num_left, $dbx_path=null, $dry_run=false)
    {
        $items = $this->listFolder($dbx_path);
        array_multisort($items, SORT_DESC);
        $to_be_deleted = [];
        for ($i = 0; $i < count($items); $i++) {
            if ($i < $num_left) continue;
            $to_be_deleted[] = $items[$i];
        }
        $deleted = [];
        foreach ($items as $path) {
            if (in_array($path, $to_be_deleted)) {
                if (!$dry_run)
                    $this->dropbox->delete($path);
                $deleted[] = $path;
            }
        }
        return $deleted;
    }
}
