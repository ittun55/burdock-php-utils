<?php
use PHPUnit\Framework\TestCase;
use Burdock\Utils\Job\NamedJob;

class JobTest extends TestCase
{

    public function test_create_job_instance_and_get_name_of_job()
    {
        $job = new NamedJob('Test Job', function($a, $b) { return $a + $b; });
        $this->assertEquals($job->getName(), 'Test Job');
    }

    public function test_process_job_function()
    {
        $job = new NamedJob('Test Job', function($a, $b) { return $a + $b; });
        $this->assertEquals($job->do(5, 3), 5 + 3);
    }
}