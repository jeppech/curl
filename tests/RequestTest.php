<?php

use Jeppech\Curl\Request;

class RequestTest extends PHPUnit_Framework_TestCase {

    /** @test */
    public function it_returns_a_response_object() {
        $request = $this->getMockBuilder('Jeppech\\Curl\\Request')
            ->setMethods(array('executeCurlRequest'))
            ->getMock();

        $request->expects($this->once())
            ->method('executeCurlRequest');

        $response = $request->get('http://somewebsite.com');

        $this->assertInstanceOf('Jeppech\\Curl\\Response', $response);
    }

    /** @test */
    public function it_can_perform_different_types_of_http_requests() {
        $request = $this->getMockBuilder('Jeppech\\Curl\\Request')
            ->setMethods(array('executeCurlRequest'))
            ->getMock();

        $request->expects($this->exactly(5))
            ->method('executeCurlRequest');

        // Predefined requests
        $request->get("http://fakesite.dk");
        $request->post("http://fakesite.dk");
        $request->put("http://fakesite.dk");
        $request->head("http://fakesite.dk");

        // Custom HTTP request
        $request->request("UPDATE", "http://fakesite.dk");
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
                ->method('executeCurlRequest');

        $request->get("invalidurl::dk");
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function it_throws_exception_on_invalid_curl_option()
    {
        $request = $this->getMockBuilder('Jeppech\\Curl\\Request')
            ->setMethods(array('executeCurlRequest'))
            ->getMock();

        $request->expects($this->never())
            ->method('executeCurlRequest');

        $request->setRequestOption("INVALID_CURLOPTION", 1);
    }

    /** @test */
    public function it_can_retrieve_http_information() {
        $request = $this->getMockBuilder('Jeppech\\Curl\\Request')
            ->setMethods(array('executeCurlRequest'))
            ->getMock();

        $request->expects($this->once())
            ->method('executeCurlRequest')
            ->will($this->returnCallback(array($this, 'fakeHttpMessage')));

        $response = $request->get("http://onlinevind.dk");

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals("200 OK", $response->getStatus());
        $this->assertCount(8, $response->getHeaders());
        $this->assertCount(0, $response->getNumberOfRedirects());
    }

    public function fakeHttpMessage() {
        return 'HTTP/1.1 200 OK
Server: nginx/1.6.2
Date: Mon, 16 Feb 2015 13:07:27 GMT
Content-Type: text/html
Content-Length: 379
Last-Modified: Sat, 29 Nov 2014 22:33:35 GMT
Connection: keep-alive
ETag: "547a49bf-17b"
Accept-Ranges: bytes

<!doctype html>

<html lang="da">
<head>
    <meta charset="utf-8">

    <title>Onlinevind</title>
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div id="container">
        <div id="content">
            <img src="images/onlinevind.png">
        </div>
        <div id="footer">

        </div>
    </div>
</body>
</html>';
    }
}
