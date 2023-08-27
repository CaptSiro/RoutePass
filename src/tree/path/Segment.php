<?php

namespace RoutePass\tree\path;

use function RoutePass\strpos_all;

class Segment {
    /** @var Part[] $parts */
    private array $parts;



    public function __construct() {
        $this->parts = [];
    }



    /**
     * @return array
     */
    public function getParts(): array {
        return $this->parts;
    }



    function addPart(Part $part): void {
        $this->parts[] = $part;
    }



    function setParam(string $name, string $regex): void {
        foreach ($this->parts as $part) {
            if ($part->type === PartType::DYNAMIC && $part->literal === $name) {
                $part->pattern = $regex;
            }
        }
    }



    function test(string $segment): SegmentTestResult {
        $count = count($this->parts);

        if ($count === 1 && $this->parts[0]->type === PartType::STATIC) {
            return new SegmentTestResult($segment === $this->parts[0]->literal, []);
        }

        $regex = "/";

        for ($i = 0; $i < $count; $i++) {
            $regex .= $this->parts[$i]->pattern();
        }

        return new SegmentTestResult(boolval(preg_match("$regex/", $segment, $matches)), $matches);
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



    public static function next(array $segments, int $position): int {
        $count = count($segments);

        for ($i = $position + 1; $i < $count; $i++) {
            if ($segments[$i] === "") {
                continue;
            }

            return $i;
        }

        return -1;
    }



    public static function isLast(array $segments, int $position): bool {
        if ($position === -1) {
            return true;
        }

        $count = count($segments);

        if ($position === $count - 1 && $segments[$position] === "") {
            return true;
        }

        for ($i = 0; $i < $count; $i++) {
            if ($segments[$i] !== "") {
                return false;
            }
        }

        return true;
    }
}
readonly class SegmentTestResult {
    public function __construct(
        public bool $hasPassed,
        public array $matches
    ) {}
}