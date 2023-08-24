<?php

namespace RoutePass\tree\path;

class Segment {
    private array $parts;



    /**
     * @return array
     */
    public function getParts(): array {
        return $this->parts;
    }



    public function __construct() {
        $this->parts = [];
    }



    function add(Part $part): void {
        $this->parts[] = $part;
    }



    static function compare(Segment $a, Segment $b): bool {
        $partsA = $a->getParts();
        $partsB = $b->getParts();

        $count = count($partsA);

        if ($count !== count($partsB)) {
            return false;
        }

        for ($i = 0; $i < $count; $i++) {
            if (Part::compare($partsA[$i], $partsB[$i]) === false) {
                return false;
            }
        }

        return true;
    }
}