<?php

namespace RoutePass\tree\traversable;

use RoutePass\tree\Node;

interface Traversable {
    function getNode(): Node;
}