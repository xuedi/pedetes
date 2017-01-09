<?php
use Pedetes\cache;
use PHPUnit\Framework\TestCase;

class cacheTest extends TestCase {

    private $ctn;

    protected function setUp() {
        parent::setUp();
        $this->ctn = new Pimple\Container();
        $this->ctn['startTime'] = microtime(true);
        $this->ctn['pebug'] = function ($ctn) { return new Pedetes\pebug($ctn); };
    }

    protected function tearDown() {
        $this->ctn = null;
    }

    public function testSet() {
        $cache = new cache($this->ctn);
        $this->assertEquals(true, $cache->set("test",1234));
    }

    public function testGet() {
        $cache = new cache($this->ctn);
        $cache->set("test",1234);
        $this->assertEquals(1234, $cache->get("test"));
    }

    public function testDelete() {
        $cache = new cache($this->ctn);
        $cache->set("test",1234);
        $cache->delete("test");
        $this->assertEquals('', $cache->get("test"));
    }

    public function testSetIfNot_A() {
        $cache = new cache($this->ctn);
        $cache->set("test",'initial');
        $cache->setIfNot("test",'extra');
        $this->assertEquals('initial', $cache->get("test"));
    }

    public function testSetIfNot_B() {
        $cache = new cache($this->ctn);
        $cache->set("test",'initial');
        $this->assertEquals(false, $cache->setIfNot("test",'extra'));
    }

    public function testSetIfNot_C() {
        $cache = new cache($this->ctn);
        $cache->setIfNot("test",'hello');
        $this->assertEquals('hello', $cache->get("test"));
    }

    public function testExist_A() {
        $cache = new cache($this->ctn);
        $cache->set("test",'initial');
        $this->assertEquals(true, $cache->exist("test"));
    }

    public function testExist_B() {
        $cache = new cache($this->ctn);
        $this->assertEquals(false, $cache->exist("test"));
    }

}
