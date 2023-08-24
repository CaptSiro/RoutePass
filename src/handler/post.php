<?php

namespace RoutePass\handler;

use Closure;

function post(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("POST"))
        ->setHandles($handlers);
}