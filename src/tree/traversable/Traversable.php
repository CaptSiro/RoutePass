<?php

namespace RoutePass\tree\traversable;

use RoutePass\tree\Node;

interface Traversable {
    function getNode(): Node;



    /**
     * @param string[] $segments
     * @param int $current
     * @param MatchStack $stack
     * @param FoundNode[] $out
     * @return void
     */
    function search(array $segments, int $current, MatchStack $stack, array &$out): void;
}