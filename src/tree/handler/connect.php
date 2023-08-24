<?php

namespace RoutePass\tree\handler;

use Closure;

function connect(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("CONNECT"))
        ->setHandles($handlers);
}