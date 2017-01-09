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

    public function testGet() {
        $cache = new cache($this->ctn);
        $cache->set("test",1234);
        $this->assertEquals(1234, $cache->get("test"));
    }
}
