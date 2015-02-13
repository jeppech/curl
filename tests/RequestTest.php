<?php
namespace Request\Test;

use Jeppech\Curl\Request;
use Jeppech\Curl\Response;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testRequestReturnsReponseObject() {
        $response = (new Request())->get("http://google.com");

        $this->assertInstanceOf("Response", $response);
    }
}