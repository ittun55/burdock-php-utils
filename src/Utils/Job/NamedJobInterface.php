<?php
namespace Burdock\Utils\Job;

interface NamedJobInterface
{
    public function __construct(string $name, callable $func);
    public function getName(): string;
    public function do($value);
}