<?php

/**
* Copyright 2012 François Kooman <fkooman@tuxed.net>
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

require_once "lib/RestService/Http/HttpRequest.php";
require_once "lib/RestService/Http/HttpRequestException.php";

require_once "lib/RestService/Http/Uri.php";
require_once "lib/RestService/Http/UriException.php";

require_once "lib/RestService/Http/Utils.php";

use \RestService\Http\HttpRequestException as HttpRequestException;
use \RestService\Http\HttpRequest as HttpRequest;
use \RestService\Http\UriException as UriException;

class HttpRequestTest extends PHPUnit_Framework_TestCase
{
    public function testHttpRequest()
    {
        $h = new HttpRequest("http://www.example.com/request", "POST");
        $h->setPostParameters(array("id" => 5, "action" => "help"));
        $this->assertEquals("http://www.example.com/request", $h->getRequestUri()->getUri());
        $this->assertEquals("POST", $h->getRequestMethod());
        $this->assertEquals("id=5&action=help", $h->getContent());
        $this->assertEquals("application/x-www-form-urlencoded", $h->getHeader("Content-type"));
        $this->assertEquals(array("id" => 5, "action" => "help"), $h->getPostParameters());
    }

    public function testHttpQueryParameters()
    {
        $h = new HttpRequest("http://www.example.com/request?action=foo&method=bar", "GET");
        $this->assertEquals(array("action" => "foo", "method" => "bar"), $h->getQueryParameters());
    }

    public function testHttpQueryParametersWithoutParameters()
    {
        $h = new HttpRequest("http://www.example.com/request", "GET");
        $this->assertEquals(array(), $h->getQueryParameters());
    }

    public function testHttpUriParametersWithPost()
    {
        $h = new HttpRequest("http://www.example.com/request?action=foo&method=bar", "POST");
        $h->setPostParameters(array("id" => 5, "action" => "help"));
        $this->assertEquals(array("action" => "foo", "method" => "bar"), $h->getQueryParameters());
        $this->assertEquals(array("id" => 5, "action" => "help"), $h->getPostParameters());
        $this->assertEquals(5, $h->getPostParameter("id"));
        $this->assertEquals("help", $h->getPostParameter("action"));
    }

    public function testSetHeaders()
    {
        $h = new HttpRequest("http://www.example.com/request", "POST");
        $h->setHeader("A", "B");
        $h->setHeader("foo", "bar");
        $this->assertEquals("B", $h->getHeader("A"));
        $this->assertEquals("bar", $h->getHeader("foo"));
        $this->assertEquals(array("A" => "B", "foo" => "bar"), $h->getHeaders(FALSE));
        $this->assertEquals(array("A: B", "foo: bar"), $h->getHeaders(TRUE));
    }

    public function testSetGetHeadersCaseInsensitive()
    {
        $h = new HttpRequest("http://www.example.com/request", "POST");
        $h->setHeader("Content-type", "application/json");
        $h->setHeader("Content-Type", "text/html"); // this overwrites the previous one
        $this->assertEquals("text/html", $h->getHeader("CONTENT-TYPE"));
    }

    /**
     * @expectedException \RestService\Http\HttpRequestException
     */
    public function testTryGetPostParametersOnGetRequest()
    {
        $h = new HttpRequest("http://www.example.com/request", "GET");
        $h->getPostParameters();
    }

    /**
     * @expectedException \RestService\Http\HttpRequestException
     */
    public function testTrySetPostParametersOnGetRequest()
    {
        $h = new HttpRequest("http://www.example.com/request", "GET");
        $h->setPostParameters(array("action" => "test"));
    }

    /**
     * @expectedException \RestService\Http\HttpRequestException
     */
/*    function testTryGetPostParametersWithoutParameters() {
        $h = new HttpRequest("http://www.example.com/request", "POST");
        $h->getPostParameters();
    }*/

    /**
     * @expectedException \RestService\Http\HttpRequestException
     */
/*    function testTryGetPostParametersWithRawContent() {
        $h = new HttpRequest("http://www.example.com/request", "POST");
        $h->setContent("Hello World!");
        $h->getPostParameters();
    }*/

    /**
     * @expectedException \RestService\Http\UriException
     */
    public function testInvalidUri()
    {
        $h = new HttpRequest("foo");
    }

    /**
     * @expectedException \RestService\Http\HttpRequestException
     */
    public function testUnsupportedRequestMethod()
    {
        $h = new HttpRequest("http://www.example.com/request", "FOO");
    }

    public function testNonExistingHeader()
    {
        $h = new HttpRequest("http://www.example.com/request");
        $this->assertNull($h->getHeader("Authorization"));
    }

    public function testForHeaderDoesNotExist()
    {
        $h = new HttpRequest("http://www.example.com/request");
        $this->assertNull($h->getHeader("Authorization"));
    }

    public function testForHeaderDoesExist()
    {
        $h = new HttpRequest("http://www.example.com/request");
        $h->setHeader("Authorization", "Bla");
        $this->assertNotNull($h->getHeader("Authorization"));
    }

    public function testForNoQueryValue()
    {
        $h = new HttpRequest("http://www.example.com/request?foo=&bar=&foobar=xyz");
        $this->assertNull($h->getQueryParameter("foo"));
        $this->assertNull($h->getQueryParameter("bar"));
        $this->assertEquals("xyz", $h->getQueryParameter("foobar"));
    }

    public function testMatchRest()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("/foo/bar/baz");
        $self = &$this;
        $this->assertTrue($h->matchRest("GET", "/:one/:two/:three", function($one, $two, $three) use ($self) {
            $self->assertEquals($one, "foo");
            $self->assertEquals($two, "bar");
            $self->assertEquals($three, "baz");
        }));
    }

    public function testMatchRestWrongMethod()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "POST");
        $h->setPathInfo("/foo/bar/baz");
        $this->assertFalse($h->matchRest("GET", "/:one/:two/:three", NULL));
    }

    public function testMatchRestNoMatch()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("/foo/bar/baz/foobar");
        $this->assertFalse($h->matchRest("GET", "/:one/:two/:three", NULL));
    }

    public function testMatchRestNoAbsPath()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("foo");
        $this->assertFalse($h->matchRest("GET", "foo", NULL));
    }

    public function testMatchRestEmptyPath()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("");
        $this->assertFalse($h->matchRest("GET", "", NULL));
    }

    public function testMatchRestEmptyRequestPath()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("/foo");
        $this->assertFalse($h->matchRest("GET", "x", NULL));
    }

    public function testMatchRestNoMatchWithoutReplacement()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("/foo");
        $this->assertFalse($h->matchRest("GET", "/bar", NULL));
    }

    public function testMatchRestNoMatchWithoutReplacementLong()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("/foo/bar/foo/bar/baz");
        $this->assertFalse($h->matchRest("GET", "/foo/bar/foo/bar/bar", NULL));
    }

    public function testMatchRestEmptyResource()
    {
        $h = new HttpRequest("http://www.example.org/api.php", "GET");
        $h->setPathInfo("/foo/");
        $this->assertFalse($h->matchRest("GET", "/foo/:bar", NULL));
        $self = &$this;
        $h->matchRestDefault(function($methodMatch, $patternMatch) use ($self) {
            $self->assertEquals(array("GET"), $methodMatch);
            $self->assertFalse($patternMatch);
        });
    }

    public function testAuthentication()
    {
        $h = new HttpRequest("http://www.example.org", "GET");
        $h->setHeader("Authorization", "Basic " . base64_encode("foo:bar"));
        $this->assertEquals("foo", $h->getBasicAuthUser());
        $this->assertEquals("bar", $h->getBasicAuthPass());
    }

}
