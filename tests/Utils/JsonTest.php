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

require_once 'lib/RestService/Utils/Json.php';
require_once 'lib/RestService/Utils/JsonException.php';

use \RestService\Utils\Json as Json;
use \RestService\Utils\JsonException as JsonException;

class JsonTest extends PHPUnit_Framework_TestCase
{
    public function testEncode()
    {
        $e = Json::enc(array("foo" => "bar"));
        $this->assertEquals('{"foo":"bar"}', $e);
    }

    public function testPrettyEncode()
    {
        $e = Json::enc(array("foo" => "bar"), TRUE);
        if (defined('JSON_PRETTY_PRINT')) {
            $this->assertEquals("{\n    \"foo\": \"bar\"\n}", $e);
        } else {
            $this->assertEquals('{"foo":"bar"}', $e);
        }
    }

    public function testDecode()
    {
        $d = Json::dec('{"foo":"bar"}');
        $this->assertEquals(array("foo" => "bar"), $d);
    }

    /**
     * @expectedException \RestService\Utils\JsonException
     * @expectedExceptionMessage Malformed UTF-8 characters, possibly incorrectly encoded
     */
    public function testBrokenEncode()
    {
        $e = Json::enc(array(iconv("UTF-8", "ISO-8859-1","îïêëì")));
    }

    /**
     * @expectedException \RestService\Utils\JsonException
     * @expectedExceptionMessage Syntax error
     */
    public function testBrokenDecode()
    {
        $e = Json::dec("'foo'");
    }
}
