<?php

use PHPUnit\Framework\TestCase;
use App\Classes\Log;

class LogTest extends TestCase
{
    private $log;

    public function setUp()
    {
        $file = new \App\Classes\File();
        $password = new \App\Classes\Password();
        $this->log = new Log($file, $password, __DIR__ . '/../../dat/log.json', __DIR__ . '/../../dat/past', 100);
    }

    public function testLogMax()
    {
        $this->assertEquals($this->log->getLogMax(), 100);
    }

    public function testDefaultPerPage()
    {
        $this->assertEquals(Log::DEFAULT_PER_PAGE, 10);
    }

}
