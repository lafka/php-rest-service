<?php

// This application will show how to use the REST service routing capabilies
// of this framework
//
// You can use cURL to test this code, some example commands, assuming the
// code is available through
// http://localhost/php-rest-service/examples/rest.php:
//
// Shows "Hello world":
// curl http://localhost/php-rest-service/examples/rest.php/hello/world
//
// Shows 404, resource not found, because it does not match the pattern
// curl http://localhost/php-rest-service/examples/rest.php/foo
//
// Shows 405, request method not allowed, as only GET requests are allowed
// curl -X POST http://localhost/php-rest-service/examples/rest.php/hello/world

require_once '../lib/RestService/Http/HttpRequest.php';
require_once '../lib/RestService/Http/HttpResponse.php';
require_once '../lib/RestService/Http/IncomingHttpRequest.php';
require_once '../lib/RestService/Http/Uri.php';

use \RestService\Http\HttpRequest as HttpRequest;
use \RestService\Http\HttpResponse as HttpResponse;
use \RestService\Http\IncomingHttpRequest as IncomingHttpRequest;

$request = NULL;
$response = NULL;

try {
    $request = HttpRequest::fromIncomingHttpRequest(new IncomingHttpRequest());

    $request->matchRest("GET", "/hello/:str", function($str) use (&$response) {
        $response = new HttpResponse(200, "text/plain");
        $response->setContent("Hello " . $str);
    });

    $request->matchRestDefault(function($methodMatch, $patternMatch) use ($request, &$response) {
        if (in_array($request->getRequestMethod(), $methodMatch)) {
            if (!$patternMatch) {
                $response = new HttpResponse(404, "text/plain");
                $response->setContent("[404] resource not found");
            }
        } else {
            $response = new HttpResponse(405, "text/plain");
            $response->setHeader("Allow", "GET");
            $response->setContent("[405] request method not allowed");
        }
    });

} catch (Exception $e) {
    $response = new HttpResponse(500);
    $response->setContent("ERROR: " . $e->getMessage());
}

$response->sendResponse();
