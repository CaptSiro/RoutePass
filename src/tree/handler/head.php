<?php


namespace RoutePass\tree\handler;

use Closure;

function head(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("HEAD"))
        ->setHandles($handlers);
}