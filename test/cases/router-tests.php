<?php

use RoutePass\Router;
use function RoutePass\handler\get;
use function RoutePass\handler\post;
use function sptf\functions\expect;
use function sptf\functions\test;



test("binds handlers to self", function () {
    $r = new Router();

    $r->bind("/",
        get(fn() => 0),
        post(fn() => 0)
    );

    $handlers = $r->getNode()->getHandlers();

    expect(count($handlers))->toBe(2);
});



test("binds handlers to direct child node", function () {
    bind("/child");
});



test("binds handlers to distant child node", function () {
    bind("/some/path/to/child");
});