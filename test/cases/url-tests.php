<?php

use RoutePass\structs\URL;
use function sptf\functions\expect;
use function sptf\functions\test;



test('creates URL from server vars', function () {
    $server_reset = set_array($_SERVER, [
        "REQUEST_URI" => "http://poggy.localhost.com/RoutePass/abc/lmao/kek?q=1234&mnoice=69420",
        "REQUEST_SCHEME" => "http",
        "HTTP_HOST" => "poggy.localhost.com",
        "QUERY_STRING" => "q=1234&nice=69420"
    ]);

    $get_reset = set_array($_GET, [
        "q" => "1234",
        "nice" => "69420"
    ]);

    $url = URL::request();

    expect($url->getHost())->toBe("poggy.localhost.com");
    expect($url->getPath())->toBe("/RoutePass/abc/lmao/kek");
    expect($url->getQueryString())->toBe("q=1234&nice=69420");
    expect($url->getProtocol())->toBe("http");

    expect($url->query->get("q"))->toBe("1234");
    expect($url->query->get("nice"))->toBe("69420");

    $server_reset();
    $get_reset();
});
