<?php
use PHPUnit\Framework\TestCase;
use Burdock\Utils\Job\Chain;
use Burdock\Utils\Job\NamedJob;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


class ChainTest extends TestCase
{
    public $logger = null;

    public function setUp(): void
    {
        $this->logger = new Logger('ChainTest');
        $this->logger->pushHandler(new StreamHandler('php://stdout'));
    }

    public function test_ラップされた値を取得する()
    {
        $chain = new Chain(1, $this->logger);
        $this->assertEquals($chain->getValue(), 1);
    }

    public function test_chained_job()
    {
        $logger = $this->logger;

        // ジョブの生成
        $addOne = new NamedJob('addOne', function($value) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return $value + 1;
        });

        $addTwo = new NamedJob('addTwo', function($value) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return $value + 2;
        });

        $sum = new NamedJob('sum', function($value, ...$args) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return array_reduce(array_merge([$value], $args), function($carry, $item) {
                return $carry + $item;
            });
        });

        $chain = (new Chain(55, $logger))
            ->process($addOne)
            ->process($addTwo)
            ->process($sum, 3, 4);
        $this->assertEquals(0, $chain->getResult());
        $this->assertEquals(65, $chain->getValue());
    }

    public function test_failed_job()
    {
        $logger = $this->logger;

        // creating jobs
        $addOne = new NamedJob('addOne', function($value) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return $value + 1;
        });

        $addTwo = new NamedJob('addTwo', function($value) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return $value + 2;
        });

        $divByZero = new NamedJob('DivByZero', function($value) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return $value / 0;
        });

        $sum = new NamedJob('sum', function($value, ...$args) use ($logger) {
            $logger->info('The arg value is : '. $value);
            return array_reduce(array_merge([$value], $args), function($carry, $item) {
                return $carry + $item;
            });
        });

        // do jobs 
        $chain = (new Chain(55, $logger))
            ->process($addOne)
            ->process($addTwo)
            ->process($divByZero)
            ->process($sum, 3, 4)
            ->process($addTwo)
            ->process($addTwo);
        $this->assertEquals(1, $chain->getResult());
        array_map(function($e) use ($logger) {
            echo $logger->error($e->getMessage().PHP_EOL);
        }, $chain->getErrors());
    }
}