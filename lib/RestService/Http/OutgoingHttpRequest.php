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

class OutgoingHttpRequest
{
    public static function makeRequest(HttpRequest $request)
    {
        $requestUri = $request->getRequestUri()->getUri();
        // if the request is towards a file URL, return the response constructed
        // from file
        if (0 === strpos($requestUri, "file:///")) {
            return HttpResponse::fromFile($requestUri);
        }

        $httpResponse = new HttpResponse();

        $curlChannel = curl_init();
        curl_setopt($curlChannel, CURLOPT_URL, $requestUri);
        curl_setopt($curlChannel, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlChannel, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlChannel, CURLOPT_TIMEOUT, 10);

        if ($request->getRequestMethod() === "POST") {
            curl_setopt($curlChannel, CURLOPT_POST, 1);
            curl_setopt($curlChannel, CURLOPT_POSTFIELDS, $request->getContent());
        }

        $basicAuthUser = $request->getBasicAuthUser();
        $basicAuthPass = $request->getBasicAuthPass();
        if (NULL !== $basicAuthUser) {
            $request->setHeader("Authorization", "Basic " . base64_encode($basicAuthUser . ":" . $basicAuthPass));
        }

        // Including HTTP headers in request
        $headers = $request->getHeaders(TRUE);

        if (!empty($headers)) {
            curl_setopt($curlChannel, CURLOPT_HTTPHEADER, $headers);
        }

        // Connect to SSL/TLS server, validate certificate and host
        if ($request->getRequestUri()->getScheme() === "https") {
            curl_setopt($curlChannel, CURLOPT_SSL_VERIFYPEER, 1);
            curl_setopt($curlChannel, CURLOPT_SSL_VERIFYHOST, 2);
        }

        // Callback to extract all the HTTP headers from the response...
        // In order to really correctly parse HTTP headers one would have to look at RFC 2616...
        curl_setopt($curlChannel, CURLOPT_HEADERFUNCTION, function($curlChannel, $header) use ($httpResponse) {
                    // Ignore Status-Line (RFC 2616, section 6.1)
                    if (0 === preg_match('|^HTTP/\d+.\d+ [1-5]\d\d|', $header)) {
                        // Only deal with header lines that contain a colon
                        if (strpos($header, ":") !== FALSE) {
                            // Only deal with header lines that contain a colon
                            list($key, $value) = explode(":", trim($header));
                            $httpResponse->setHeader(trim($key), trim($value));
                        }
                    }

                    return strlen($header);
                });

        $output = curl_exec($curlChannel);
        if ($errorNumber = curl_errno($curlChannel)) {
            throw new OutgoingHttpRequestException(curl_error($curlChannel));
        }
        $httpResponse->setStatusCode(curl_getinfo($curlChannel, CURLINFO_HTTP_CODE));
        $httpResponse->setContent($output);
        curl_close($curlChannel);

        return $httpResponse;
    }

}
