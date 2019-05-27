<?php
namespace Burdock\Utils\Job;

class NamedJob implements NamedJobInterface
{
    private $_name = null;
    private $_func = null;
 
    public function __construct(string $name, callable $func)
    {
        if (!$name) throw new Exception('Job name is required.');
        if (!$func) throw new Exception('Job function is required.');
        $this->_name = $name;
        $this->_func = $func;
    }
 
    public function getName(): string
    {
        return $this->_name;
    }
 
    /**
     * 単項関数しか扱えないのは、使いづらいので可変長引数を受けられるようにしておく
     */
    public function do($value, ...$args)
    {
        $func = $this->_func;
        return $func($value, ...$args);
    }
}