<?php

namespace RoutePass\tree\path;



readonly class Part {
    public function __construct(
        public PartType $type,
        public string $pattern
    ) {}



    static function compare(Part $a, Part $b): bool {
        return $a->type === $b->type
            && $a->pattern === $b->pattern;
    }
}