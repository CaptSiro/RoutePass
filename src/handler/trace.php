<?php

namespace RoutePass\handler;

use Closure;

function trace(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("TRACE"))
        ->setHandles($handlers);
}
