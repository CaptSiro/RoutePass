<?php

namespace RoutePass\tree\handler;

use Closure;

function patch(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("PATCH"))
        ->setHandles($handlers);
}
