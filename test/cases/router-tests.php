<?php

use RoutePass\Router;
use RoutePass\tree\path\Segment;
use RoutePass\tree\traversable\MatchStack;
use function RoutePass\handler\get;
use function RoutePass\handler\post;
use function RoutePass\tree\path\path;
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



test("finds all leaf nodes", function () {
    $r = new Router();

    $r->bind("/[id]/[bar]", get(fn() => 0));
    $r->bind("/[id]/[foo]", get(fn() => 0));


    $s = explode("/", "/69/any");
    $o = [];

    $r->search(
        $s,
        Segment::next($s, 0),
        new MatchStack(),
        $o
    );

    expect(count($o))->toBe(2);
    expect($o[0]->matches)->toBe([
        "id" => "69",
        "bar" => "any"
    ])->compare(fn($a, $b) => array_equal($a, $b));
    expect($o[1]->matches)->toBe([
        "id" => "69",
        "foo" => "any"
    ])->compare(fn($a, $b) => array_equal($a, $b));
});



test("skip typed search", function () {
    $r = new Router();

    $r->bind("/[id]/[bar]",
        get(fn() => 0)
    );
    $r->bind(path("/[id]/[foo]")->param("id", "[0-9]+"),
        get(fn() => 0)
    );


    $s = explode("/", "/foo/any");
    $o = [];

    $r->search(
        $s,
        Segment::next($s, 0),
        new MatchStack(),
        $o
    );

    expect(count($o))->toBe(1);
    expect($o[0]->matches)->toBe([
        "id" => "foo",
        "bar" => "any"
    ])->compare(fn($a, $b) => array_equal($a, $b));
});