<?php

use Jeppech\Curl\Request;

class RequestTest extends PHPUnit_Framework_TestCase {

    public $website = 'http://google.com';

    /** @test */
    public function it_returns_a_response_object() {
        $request = $this->getMockBuilder('Jeppech\\Curl\\Request')
            ->setMethods(array('executeCurlRequest'))
            ->getMock();

        $request->expects($this->once())
            ->method('executeCurlRequest')
            ->will($this->returnValue("HTTP BODY"));

        $response = $request->get('http://somewebsite.com');

        $this->assertInstanceOf('Jeppech\\Curl\\Response', $response);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_throws_exception_on_invalid_url()
    {

        $request = $this->getMockBuilder('Jeppech\\Curl\\Request')
                        ->setMethods(array('executeCurlRequest'))
                        ->getMock();

        $request->expects($this->never())
                ->method('executeCurlRequest')
                ->will($this->returnValue("SUMTIN"));

        $request->get('onlinevind.aaa');

    }
}
