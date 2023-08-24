<?php

namespace RoutePass\tree\path;

class Path {
    private array $segments;



    /**
     * @return array
     */
    public function getSegments(): array {
        return $this->segments;
    }



    public function __construct() {
        $this->segments = [];
    }



    /**
     * @param array $sections
     */
    public function addSegment(Segment $section): void {
        $this->segments[] = $section;
    }



    private int $index = 0;

    function hasNext(): bool {
        return isset($this->segments[$this->index]);
    }



    function next(): Segment {
        $this->index++;
        return $this->segments[$this->index - 1];
    }



    function rewind(): void {
        $this->index = 0;
    }



    static function fromRaw(array $segments): self {
        $p = new self();

        foreach ($segments as $parts) {
            $s = new Segment();

            foreach ($parts as $part) {
                $s->add(new Part(...$part));
            }

            $p->addSegment($s);
        }

        return $p;
    }



    static function compare(Path $a, Path $b): bool {
        $segmentsA = $a->getSegments();
        $segmentsB = $b->getSegments();

        $count = count($segmentsA);

        if ($count !== count($segmentsB)) {
            return false;
        }

        for ($i = 0; $i < $count; $i++) {
            if (Segment::compare($segmentsA[$i], $segmentsB[$i]) === false) {
                return false;
            }
        }

        return true;
    }
}