<?php

namespace RoutePass\tree;

use RoutePass\tree\handler\Handler;
use RoutePass\tree\path\parser\Parser;
use RoutePass\tree\traversable\Traversable;
use RoutePass\tree\traversable\TraversableTrait;

readonly class Router implements Traversable {
    use TraversableTrait;




    private Node $node;



    /**
     * @return Node
     */
    public function getNode(): Node {
        return $this->node;
    }



    public function __construct() {
        $this->node = new Node();
    }



    function bind(string $path, Handler $handler, Handler ...$handlers): void {
        $parsedPath = Parser::parse($path);

        $node = $this->node;
        while ($parsedPath->hasNext()) {
            $node = $node->create($parsedPath->next());
        }

        array_unshift($handlers, $handler);

        $node->assign($handlers);
    }
}