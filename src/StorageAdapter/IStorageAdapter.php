<?php
namespace Burdock\StorageAdapter;

interface IStorageAdapter
{
    public function getList(string $path): array;
    public function getFile(string $src, string $dst): string;
    public function saveFile(string $src, string $dst): bool;
    public function delete(string $remote): bool;
    public function createFolder(string $path): bool;
}