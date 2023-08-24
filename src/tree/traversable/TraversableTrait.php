<?php

namespace RoutePass\tree\traversable;

use RoutePass\tree\Node;
use RoutePass\tree\path\Path;

trait TraversableTrait {
    function walk(Path $path, Node $start): Node|null {
        $path->rewind();

        while ($path->hasNext()) {
            $start = $start->find($path->next());

            if ($start === null) {
                return null;
            }
        }

        return $start;
    }
}