<?php
namespace Burdock\Utils\Job;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Chain
{
    const PROCESS_FAILED_MSG  = 'This process was failed.';
    const PROCESS_SKIPPED_MSG = 'This process was skipped because previous process was failed.';
    private $_value  = null;
    private $_errors = null;
    private $_logger = null;
 
    public function __construct($value=null, ?LoggerInterface $logger=null, array $errors=[])
    {
        $this->_value = $value;
        $this->_logger = (is_null($logger)) ? new NullLogger() : $logger;
        $this->_errors = $errors;
    }
 
    public function process(NamedJobInterface $func, ...$args) : Chain
    {
        $this->_logger->info($func->getName());

        if ($this->_value instanceof Failed) {
            $this->_logger->warning(static::PROCESS_SKIPPED_MSG);
            return $this;
        }

        try {
            $this->_logger->debug('  Wrapped value: ' . var_export($this->_value, true));
            return new Chain($func->do($this->_value, ...$args), $this->_logger, $this->_errors);
        } catch(\Exception $e) {
            $this->_logger->warning(static::PROCESS_FAILED_MSG);
            $this->_logger->debug('  Wrapped value: ' . var_export($this->_value, true));
            return new Chain(new Failed(), $this->_logger, array_merge($this->_errors, [$e]));
        }
    }
 
    public function getValue()
    {
        if ($this->_value instanceof Failed)
            return static::PROCESS_FAILED_MSG;
        return $this->_value;
    }
 
    public function getResult()
    {
        return (count($this->_errors) == 0) ? 0 : 1;
    }
 
    public function getErrors()
    {
        return $this->_errors;
    }
}