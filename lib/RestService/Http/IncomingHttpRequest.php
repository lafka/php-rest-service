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

namespace RestService\Http;

class IncomingHttpRequest
{
    public function __construct()
    {
        $required_keys = array("SERVER_NAME", "SERVER_PORT", "REQUEST_URI", "REQUEST_METHOD");
        foreach ($required_keys as $r) {
            if (!array_key_exists($r, $_SERVER) || empty($_SERVER[$r])) {
                throw new IncomingHttpRequestException("missing (one or more) required environment variables");
            }
        }
    }

    public function getRequestMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function getPathInfo()
    {
        return array_key_exists('PATH_INFO', $_SERVER) ? $_SERVER['PATH_INFO'] : NULL;
    }

    public function getRequestUri()
    {
        // scheme
        $proxy = FALSE;
        if (array_key_exists("HTTPS", $_SERVER) && !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
            $scheme = "https";
        } elseif (array_key_exists("HTTP_X_FORWARDED_PROTO", $_SERVER) && "https" === $_SERVER["HTTP_X_FORWARDED_PROTO"]) {
            // HTTPS to HTTP proxy is present
            $scheme = "https";
            $proxy = TRUE;
        } else {
            $scheme = "http";
        }

        // server name
        if (filter_var($_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === FALSE) {
            $name = $_SERVER['SERVER_NAME'];
        } else {
            $name = '[' . $_SERVER['SERVER_NAME'] . ']';
        }

        // server port
        if (($_SERVER['SERVER_PORT'] === "80" && ($scheme === "http" || $proxy)) || ($_SERVER['SERVER_PORT'] === "443" && $scheme === "https")) {
            $port = "";
        } else {
            $port = ":" . $_SERVER['SERVER_PORT'];
        }

        return $scheme . "://" . $name . $port . $_SERVER['REQUEST_URI'];
    }

    public function getContent()
    {
        if ($_SERVER['REQUEST_METHOD'] !== "POST" && $_SERVER['REQUEST_METHOD'] !== "PUT") {
            return NULL;
        }
        if (array_key_exists("CONTENT_LENGTH", $_SERVER) && $_SERVER['CONTENT_LENGTH'] > 0) {
            return $this->getRawContent();
        }

        return NULL;
    }

    public function getRawContent()
    {
        return file_get_contents("php://input");
    }

    public function getBasicAuthUser()
    {
        if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
            return $_SERVER['PHP_AUTH_USER'];
        }

        return NULL;
    }

    public function getBasicAuthPass()
    {
        if (array_key_exists('PHP_AUTH_PW', $_SERVER)) {
            return $_SERVER['PHP_AUTH_PW'];
        }

        return NULL;
    }

    private function _getHeaderValue(array $h, $key)
    {
        $headerKeys = array_keys($h);
        $keyPositionInArray = array_search(strtolower($key), array_map('strtolower', $headerKeys));

        return FALSE !== $keyPositionInArray ? $h[$headerKeys[$keyPositionInArray]] : FALSE;
    }

    public function getRequestHeaders()
    {
        $requestHeaders = $_SERVER;
        if (function_exists("apache_request_headers")) {
            // add the AUTHORIZATION header to the request headers, this is
            // a special header not added to $_SERVER by PHP by default on
            // Apache
            $v = $this->_getHeaderValue(apache_request_headers(), "AUTHORIZATION");
            if (FALSE !== $v) {
                $requestHeaders['AUTHORIZATION'] = $v;
            }
        } else {
            // On other web servers the HTTP_AUTHORIZATION header may be set,
            // which we now make available under the AUTHORIZATION key as well
            $v = $this->_getHeaderValue($requestHeaders, "HTTP_AUTHORIZATION");
            if (FALSE !== $v) {
                $requestHeaders['AUTHORIZATION'] = $v;
            }
        }

        return $requestHeaders;
    }

}
