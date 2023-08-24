<?php

namespace RoutePass;

use RoutePass\handler\Handler;
use RoutePass\tree\Node;
use RoutePass\tree\path\parser\Parser;
use RoutePass\tree\traversable\Traversable;
use function RoutePass\tree\traversable\walk;

require_once __DIR__ . "/tree/traversable/Traversable.php";

readonly class Router implements Traversable {
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



    function execute(string $path, string $httpMethod) {
        $node = walk($this->node, Parser::parse($path));


    }
}