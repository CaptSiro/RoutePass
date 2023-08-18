<?php

use RoutePass\structs\URL;

$paths = glob(__DIR__ . "/../src/**/*.php");

foreach ($paths as $path) {
    require_once $path;
}

require_once __DIR__ . "/test-functions.php";



it('creates URL from server vars', function () {
    $reset = setServerVars([
        "REQUEST_URI" => "http://poggy.localhost.com/RoutePass/abc/lmao/kek?q=1234&mnoice=69420",
        "REQUEST_SCHEME" => "http",
        "HTTP_HOST" => "poggy.localhost.com",
        "QUERY_STRING" => "q=1234&mnoice=69420"
    ]);

    $url = URL::request();

    expect($url->getHost())->toBe("poggy.localhost.com")
        ->and($url->getPath())->toBe("/RoutePass/abc/lmao/kek")
        ->and($url->getQuery())->toBe("q=1234&mnoice=69420")
        ->and($url->getProtocol())->toBe("http");

    $reset();
});
