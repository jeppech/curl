<?php

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
            ->setMethods(array('executeCurlRequest', 'initializeCurl'))
            ->getMock();

        $request->expects($this->once())
            ->method('executeCurlRequest')
            ->will($this->returnCallback(array($this, 'HttpMessageNoRedirect')));

        $response = $request->get("http://onlinevind.dk");

        $this->assertEquals(200, $response->getCode());
        $this->assertEquals("200 OK", $response->getStatus());

        $this->assertCount(8, $response->getHeaders());
        $this->assertEquals(0, $response->getNumberOfRedirects());
    }

    public function HttpMessageNoRedirect() {
        return <<<FAKEHTTP
HTTP/1.1 200 OK
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
</html>
FAKEHTTP;
    }

    public function HttpMessageOneRedirect() {
    return <<<FAKEHTTP
HTTP/1.1 303 See other
Date: Tue, 17 Feb 2015 20:20:48 GMT
Server: Apache
X-Powered-By: PHP/5.5.21
Set-Cookie: 5e8627afafd16a920dac4dbb644715a5=ue1gprimq6rb5r4qsb8sk28jb2; path=/; HttpOnly
Location: http://care4all.dk/da/
Connection: close
Content-Type: text/html; charset=utf-8
Set-Cookie: SERVERID=; path=/

HTTP/1.1 200 OK
Date: Tue, 17 Feb 2015 20:20:48 GMT
Server: Apache
X-Powered-By: PHP/5.5.21
Set-Cookie: 76726d50abd3edd601ecfbc19fe61c87=da-DK; path=/
Set-Cookie: 76726d50abd3edd601ecfbc19fe61c87=da-DK
P3P: CP="NOI ADM DEV PSAi COM NAV OUR OTRo STP IND DEM"
Expires: Mon, 1 Jan 2001 00:00:00 GMT
Last-Modified: Tue, 17 Feb 2015 20:20:48 GMT
Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
Pragma: no-cache
Connection: close
Content-Type: text/html; charset=utf-8
Set-Cookie: SERVERID=; path=/

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
</html>
FAKEHTTP;
}
}

//http://pastebin.com/7VVz4jMc