<?php

use PHPUnit\Framework\TestCase;
use \App\Classes\Pagination;

class PaginationTest extends TestCase
{
    private $pagination;

    public function setUp() {
        $this->pagination = new Pagination();
    }

    public function testSetting()
    {
        $this->pagination->setting(1, 1, 1);
        $this->assertEquals($this->pagination->getCount(), 1);
        $this->assertEquals($this->pagination->getCurrentPage(), 1);
        $this->assertEquals($this->pagination->getPerPage(), 1);

        $this->pagination->setting(1.1, 1.1, 1.1);
        $this->assertEquals($this->pagination->getCount(), 1);
        $this->assertEquals($this->pagination->getCurrentPage(), 1);
        $this->assertEquals($this->pagination->getPerPage(), 1);

        $this->pagination->setting('hoge', 'hoge', 'hoge');
        $this->assertEquals($this->pagination->getCount(), 0);
        $this->assertEquals($this->pagination->getCurrentPage(), 0);
        $this->assertEquals($this->pagination->getPerPage(), 1);

        $this->pagination->setting(10, 0, 10);
        $this->assertEquals($this->pagination->getCurrentPage(), 0);
        $this->pagination->setting(10, -1, 10);
        $this->assertEquals($this->pagination->getCurrentPage(), 0);

        $this->pagination->setting(10, 0, 0);
        $this->assertEquals($this->pagination->getPerPage(), 1);
        $this->pagination->setting(10, 0, -1);
        $this->assertEquals($this->pagination->getPerPage(), 1);
    }

    public function testOffset() {
        $this->pagination->setting(10, 1, 10);
        $this->assertEquals($this->pagination->getOffset(), 10);
        $this->pagination->setting(10, 0, 10);
        $this->assertEquals($this->pagination->getOffset(), 0);
        $this->pagination->setting(10, -1, 10);
        $this->assertEquals($this->pagination->getOffset(), 0);
    }

    public function testIsFirstPage() {
        $this->pagination->setting(10, 0, 2);
        $this->assertFalse($this->pagination->isFirstPage());

        $this->pagination->setting(10, 1, 2);
        $this->assertTrue($this->pagination->isFirstPage());
    }

    public function testIsLastPage() {
        $this->pagination->setting(10, 3, 2);
        $this->assertTrue($this->pagination->isLastPage());
        $this->pagination->setting(10, 4, 2);
        $this->assertFalse($this->pagination->isLastPage());
    }

    public function testLastPageNum() {
        $this->pagination->setting(10, 0, 3);
        $this->assertEquals($this->pagination->lastPageNum(), 3);
        $this->pagination->setting(10, 0, 2);
        $this->assertEquals($this->pagination->lastPageNum(), 4);
        $this->pagination->setting(10, 0, 1);
        $this->assertEquals($this->pagination->lastPageNum(), 9);
        $this->pagination->setting(0, 0, 0);
        $this->assertEquals($this->pagination->lastPageNum(), 0);
        $this->pagination->setting(0, -1, 0);
        $this->assertEquals($this->pagination->lastPageNum(), 0);
    }

    public function testIsEnable() {
        $this->pagination->setting(2, 0, 1);
        $this->assertTrue($this->pagination->isEnable());
        $this->pagination->setting(1, 0, 1);
        $this->assertFalse($this->pagination->isEnable());
        $this->pagination->setting(0, 0, 1);
        $this->assertFalse($this->pagination->isEnable());
    }
}