<?php

namespace RoutePass\tree\traversable;

use RoutePass\tree\Node;
use RoutePass\tree\path\Path;

interface Traversable {
    function getNode(): Node;

    function walk(Path $path, Node $start): Node|null;
}