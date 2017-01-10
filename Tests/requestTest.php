<?php
use Pedetes\request;
use PHPUnit\Framework\TestCase;

class requestTest extends TestCase {

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



    public function testNoName() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->NoNameWasGiven");
        $request = new request($this->ctn);
        $request->value();
    }
    public function testWithoutValidation() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->ValidationIsMissing");
        $request = new request($this->ctn);
        $request->setMock("requestField",'1234');
        $request->name("requestField")->value();
    }

/*
    public function testValidateEmail_A() {
        $request = new request($this->ctn);
        $request->setMock("email",'test.test@test.com');
        $this->assertEquals('test.test@test.com', $request->name("email")->validateEmail()->value());
    }
*/

    public function testValidateNumberWithoutDefault() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->NoDefaultWasGiven");
        $request = new request($this->ctn);
        $request->setMock("number",'test');
        $this->assertEquals(10, $request->name("number")->validateNumber()->value());
    }
    public function testValidateNumberValidate_OK_10() {
        $request = new request($this->ctn);
        $request->setMock("number",10);
        $this->assertEquals(10, $request->name("number")->validateNumber()->default(100)->value());
    }
    public function testValidateNumberValidate_OK_zero() {
        $request = new request($this->ctn);
        $request->setMock("number",0);
        $this->assertEquals(0, $request->name("number")->validateNumber()->default(100)->value());
    }
    public function testValidateNumberValidate_OK_minus() {
        $request = new request($this->ctn);
        $request->setMock("number",-42);
        $this->assertEquals(-42, $request->name("number")->validateNumber()->default(100)->value());
    }
    public function testValidateNumberValidate_FAIL_text() {
        $request = new request($this->ctn);
        $request->setMock("number",'test');
        $this->assertEquals(100, $request->name("number")->validateNumber()->default(100)->value());
    }
    public function testValidateNumberValidateStrict_OK() {
        $request = new request($this->ctn);
        $request->setMock("number",10);
        $this->assertEquals(10, $request->name("number")->strict()->validateNumber()->value());
    }
    public function testValidateNumberValidateStrict_FAIL() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->ValidationFailed");
        $request = new request($this->ctn);
        $request->setMock("number",'test');
        $this->assertEquals(10, $request->name("number")->strict()->validateNumber()->value());
    }
    public function testValidateNumberValidateStrictDefault_OK() {
        $request = new request($this->ctn);
        $request->setMock("number",10);
        $this->assertEquals(10, $request->name("number")->strict()->default(100)->validateNumber()->value());
    }
    public function testValidateNumberValidateStrictDefault_FAIL() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->ValidationFailed");
        $request = new request($this->ctn);
        $request->setMock("number",'test');
        $this->assertEquals(10, $request->name("number")->strict()->default(10)->validateNumber()->value());
    }


    public function testValidateArrayWithoutDefault() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->NoDefaultWasGiven");
        $request = new request($this->ctn);
        $request->setMock("number",'test');
        $this->assertEquals(10, $request->name("number")->validateNumber()->value());
    }
    public function testValidateArrayWithoutOptions() {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("request::_get->NoArrayOptionsWhereGiven");
        $request = new request($this->ctn);
        $request->setMock("value",'hello');
        $this->assertEquals('hello', $request->name("value")->default(100)->validateArray()->value());
    }
    public function testValidateArrayWithOptions_OK() {
        $request = new request($this->ctn);
        $request->setMock("lang",'de');
        $options = array('de','en','zh','fr');
        $this->assertEquals('de', $request->name("lang")->default('en')->array($options)->validateArray()->value());
    }
    public function testValidateArrayWithOptions_FAIL() {
        $request = new request($this->ctn);
        $request->setMock("value",'hipster');
        $options = array('hello','world','how','are','you');
        $this->assertEquals(100, $request->name("value")->default(100)->array($options)->validateArray()->value());
    }

}
