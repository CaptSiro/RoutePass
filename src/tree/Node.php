<?php

namespace RoutePass\tree;

use RoutePass\handler\Handler;
use RoutePass\tree\path\Segment;
use RoutePass\tree\traversable\Traversable;

require_once __DIR__ . "/traversable/Traversable.php";

class Node implements Traversable {
    private Segment $segment;
    /** @var Traversable[] $nodes */
    private array $nodes;
    /** @var Handler[] $handlers */
    private array $handlers;



    public function __construct() {
        $this->handlers = [];
        $this->nodes = [];
    }



    /**
     * @param Segment $segment
     */
    public function setSegment(Segment $segment): void {
        $this->segment = $segment;
    }



    /**
     * @return Segment
     */
    public function getSegment(): Segment {
        return $this->segment;
    }



    /**
     * @return array
     */
    public function getHandlers(): array {
        return $this->handlers;
    }



    function find(Segment $segment): Node|null {
        foreach ($this->nodes as $n) {
            if (Segment::compare($n->getNode()->getSegment(), $segment)) {
                return $n;
            }
        }

        return null;
    }



    function create(Segment $segment): Node {
        $found = $this->find($segment);

        if ($found !== null) {
            return $found;
        }

        $new = new Node();

        $new->setSegment($segment);
        $this->nodes[] = $new;

        return $new;
    }



    function assign(array $handlers): void {
        $this->handlers = array_merge($this->handlers, $handlers);
    }



    function getNode(): Node {
        return $this;
    }
}