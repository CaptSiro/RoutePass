<?php

namespace RoutePass\tree\path;



class Part {
    public function __construct(
        public readonly PartType $type,
        public readonly string $literal,
        public string $pattern = "(.*)"
    ) {}



    static function compare(Part $a, Part $b): bool {
        return $a->type === $b->type
            && $a->literal === $b->literal
            && $a->pattern === $b->pattern;
    }
}