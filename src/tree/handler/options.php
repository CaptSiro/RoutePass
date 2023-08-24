<?php

namespace RoutePass\tree\handler;

use Closure;

function options(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("OPTIONS"))
        ->setHandles($handlers);
}
