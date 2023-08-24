<?php

namespace RoutePass\handler;

use Closure;

function delete(Closure $handler, Closure ...$handlers): Handler {
    array_unshift($handlers, $handler);

    return (new Handler("DELETE"))
        ->setHandles($handlers);
}