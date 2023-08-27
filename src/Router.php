<?php

namespace RoutePass;

use RoutePass\handler\Handler;
use RoutePass\request\structs\URL;
use RoutePass\tree\Node;
use RoutePass\tree\path\parser\Parser;
use RoutePass\tree\path\Path;
use RoutePass\tree\path\Segment;
use RoutePass\tree\traversable\MatchStack;
use RoutePass\tree\traversable\Traversable;

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



    function bind(Path|string $path, Handler $handler, Handler ...$handlers): void {
        $parsedPath = $path instanceof Path
            ? $path
            : Parser::parse($path);

        $node = $this->node;
        while ($parsedPath->hasNext()) {
            $node = $node->create($parsedPath->next());
        }

        array_unshift($handlers, $handler);

        $node->assign($handlers);
    }



    function search(array $segments, int $current, MatchStack $stack, array &$out): void {
        $this->node->search($segments, $current, $stack, $out);
    }
}