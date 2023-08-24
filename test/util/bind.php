<?php

use RoutePass\handler\Handler;
use RoutePass\Router;
use RoutePass\tree\path\parser\Parser;
use function RoutePass\handler\get;
use function RoutePass\handler\post;
use function RoutePass\tree\traversable\walk;
use function sptf\functions\expect;
use function sptf\functions\fail;

function bind(string $path, Handler ...$handlers): Router {
    if (empty($handlers)) {
        $handlers = [
            get(fn() => 0),
            post(fn() => 0)
        ];
    }

    $r = new Router();

    $r->bind($path, ...$handlers);

    $node = walk($r->getNode(), Parser::parse($path));

    if ($node === null) {
        fail("Failed to find node for path: '$path'");
        return $r;
    }

    expect(count($node->getHandlers()))->toBe(2);

    return $r;
}