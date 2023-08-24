<?php

namespace RoutePass\tree\handler;

use Closure;

function put(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("PUT"))
        ->setHandles($handlers);
}
