<?php

namespace RoutePass\handler;

use Closure;

function get(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("GET"))
        ->setHandles($handlers);
}