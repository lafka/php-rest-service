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

require_once 'lib/RestService/Http/HttpRequest.php';
require_once 'lib/RestService/Http/HttpResponse.php';
require_once 'lib/RestService/Http/HttpResponseException.php';

require_once 'lib/RestService/Http/OutgoingHttpRequest.php';
require_once 'lib/RestService/Http/OutgoingHttpRequestException.php';
require_once 'lib/RestService/Http/Uri.php';

use \RestService\Http\HttpRequest as HttpRequest;
use \RestService\Http\OutgoingHttpRequest as OutgoingHttpRequest;
use \RestService\Http\OutgoingHttpRequestException as OutgoingHttpRequestException;

class OutgoingHttpRequestTest extends PHPUnit_Framework_TestCase
{

    private $_filePath;

    public function setUp()
    {
        $this->_filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data";
    }

    public function testSimpleFileRequest()
    {
        $h = new HttpRequest("file://" . $this->_filePath . DIRECTORY_SEPARATOR . "simple.txt");
        $response = OutgoingHttpRequest::makeRequest($h);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals("Hello World", $response->getContent());
        $this->assertEquals("text/plain", $response->getContentType());
    }

}
