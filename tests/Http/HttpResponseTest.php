<?php

/**
* Copyright 2013 François Kooman <fkooman@tuxed.net>
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
* http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/

require_once 'lib/RestService/Http/HttpResponse.php';
require_once 'lib/RestService/Http/HttpResponseException.php';

//require_once 'lib/RestService/Http/Uri.php';
//require_once 'lib/RestService/Http/UriException.php';

use \RestService\Http\HttpResponseException as HttpResponseException;
use \RestService\Http\HttpResponse as HttpResponse;
//use \RestService\Http\UriException as UriException;

class HttpResponseTest extends PHPUnit_Framework_TestCase
{

    private $_filePath;

    public function setUp()
    {
        $this->_filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data";
    }

    public function testHttpResponse()
    {
        $h = new HttpResponse();
        $this->assertEquals(200, $h->getStatusCode());
        $this->assertEquals("text/html", $h->getContentType());
        $this->assertEquals("", $h->getContent());
        $this->assertEquals(NULL, $h->getHeader("Foo"));
    }

    public function testHttpResponseFromFile()
    {
        $h = HttpResponse::fromFile($this->_filePath . DIRECTORY_SEPARATOR . "simple.txt");
        $this->assertEquals(200, $h->getStatusCode());
        $this->assertEquals("text/plain", $h->getContentType());
        $this->assertEquals("Hello World", $h->getContent());
        $this->assertEquals(11, $h->getHeader("Content-Length"));
    }

    public function testHttpResponseBearerFromFile()
    {
        $h = HttpResponse::fromFile($this->_filePath . DIRECTORY_SEPARATOR . "bearer.txt");
        $this->assertEquals(401, $h->getStatusCode());
        $this->assertEquals("application/json", $h->getContentType());
        $this->assertEquals('Bearer realm="VOOT Proxy",error="invalid_token",error_description="the token is not active"', $h->getHeader("WWW-AuThEnTiCaTe"));
        $this->assertEquals('{"error":"invalid_token","error_description":"the token is not active"}', $h->getContent());
    }

    public function testHttpResponseEmptyResponseFromFile()
    {
        $h = HttpResponse::fromFile($this->_filePath . DIRECTORY_SEPARATOR . "empty_response.txt");
        $this->assertEquals(200, $h->getStatusCode());
        $this->assertEquals("text/html", $h->getContentType());
        $this->assertEquals("", $h->getContent());
    }
}
