<?php

namespace RoutePass\tree\traversable;

use RoutePass\tree\Node;

readonly class FoundNode {
    public function __construct(
        public array $matches,
        public Node  $node
    ) {}
}