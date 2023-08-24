<?php

use RoutePass\tree\handler\Handler;
use RoutePass\tree\path\parser\Parser;
use RoutePass\tree\Router;
use function RoutePass\tree\handler\get;
use function RoutePass\tree\handler\post;
use function sptf\functions\fail;
use function sptf\functions\expect;

function bind(string $path, Handler ...$handlers): Router {
    if (empty($handlers)) {
        $handlers = [
            get(fn() => 0),
            post(fn() => 0)
        ];
    }

    $r = new Router();

    $r->bind($path, ...$handlers);

    $node = $r->walk(Parser::parse($path), $r->getNode());

    if ($node === null) {
        fail("Failed to find node for path: '$path'");
        return $r;
    }

    expect(count($node->getHandlers()))->toBe(2);

    return $r;
}