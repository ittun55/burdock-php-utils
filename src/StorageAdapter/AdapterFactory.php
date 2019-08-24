<?php
declare(strict_types=1);

namespace Burdock\StorageAdapter;

class AdapterFactory
{
    const DROPBOX = 'dropbox';

    public static function getInstance(string $type, DropboxConfig $config): IStorageAdapter
    {
        if ($type == self::DROPBOX) {
            return new DropboxAdapter($config);
        }
    }
}