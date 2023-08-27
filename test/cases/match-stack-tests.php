<?php

use RoutePass\tree\traversable\MatchStack;
use function sptf\functions\expect;
use function sptf\functions\test;



test("merges match arrays", function () {
    $stack = new MatchStack();

    $stack->push([
        0 => "hello",
        "greeting" => "hello",
    ]);
    $stack->push([
        0 => "hello",
        "name" => "John",
    ]);
    $stack->push([
        0 => "hello",
        "id" => "69420",
    ]);

    $matches = $stack->merge();
    expect($matches)->toBe([
        "greeting" => "hello",
        "name" => "John",
        "id" => "69420",
    ])->compare(fn($a, $b) => array_equal($a, $b));
});