<?php

namespace RoutePass\tree\handler;

use Closure;

function trace(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("TRACE"))
        ->setHandles($handlers);
}
