<?php

namespace RoutePass\tree\traversable;

class MatchStack {
    private array $stack;



    public function __construct() {
        $this->stack = [];
    }



    function push(array $array): void {
        $this->stack[] = $array;
    }



    function pop(): void {
        array_pop($this->stack);
    }



    function merge(): array {
        $out = [];
        $count = count($this->stack);

        for ($i = 0; $i < $count; $i++) {
            foreach ($this->stack[$i] as $key => $match) {
                if (gettype($key) === "string") {
                    $out[$key] = $match;
                }
            }
        }

        return $out;
    }
}