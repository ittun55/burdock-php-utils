<?php
namespace Burdock\Job;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class DelayedChain
{
    const PROCESS_FAILED_MSG  = 'This process was failed.';
    const PROCESS_SKIPPED_MSG = 'This process was skipped because previous process was failed.';
    private $_processes = [];
    private $_value  = null;
    private $_errors = null;
    private $_logger = null;
 
    public function __construct($value, ?LoggerInterface $logger=null, array $errors=[])
    {
        if (is_null($value)) {
            throw new \InvalidArgumentException('The $value can not be null');
        }

        $this->_value = $value;

        if (is_null($logger)) {
            $this->_logger = new NullLogger();
        } else {
            $this->_logger = $logger;
        }

        $this->_errors = $errors;
    }
 
    public function process(NamedJobInterface $func, ...$args) : DelayedChain
    {
        $this->_processes[] = [$func, $args];

    }

    public function execute() : DelayedChain
    {
        $logger = $this->_logger;
        $errors = $this->_errors;
        $this->_value = array_reduce($this->_processes, function($carry, $func_args) use ($logger, $errors) {
            list($func, $args) = $func_args;
            $logger->info($func->getName());

            if ($carry instanceof Failed) {
                $logger->warn(static::PROCESS_SKIPPED_MSG);
                return $carry;
            }
    
            try {
                $logger->debug('  Wrapped value: ' . var_export($carry, true));
                return new DelayedChain($func->do($carry, ...$args), $logger, $errors);
            } catch(\Exception $e) {
                $logger->warn(static::PROCESS_FAILED_MSG);
                $logger->debug('  Wrapped value: ' . var_export($carry, true));
                return new DelayedChain(new Failed(), $logger, array_merge($errors, [$e]));
            }
        });
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