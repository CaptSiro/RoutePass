<?php

namespace RoutePass\map;

use Closure;
use RoutePass\map\descriptors\Exists;
use RoutePass\map\descriptors\Get;
use RoutePass\map\descriptors\GetStrict;
use stdClass;

readonly class StrictMap implements Get, GetStrict, Exists {
    private Map $map;

    public function __construct(
        private Closure $notDefinedFn,
        array $array,
    ) {
        $this->map = new Map($array);
    }



    function get(string $name, $or = null): mixed {
        return $this->map->get($name, $or);
    }



    function getStrict(string $name): mixed {
        if (!$this->exists($name)) {
            $this->notDefinedFn->call(new stdClass, $name);
        }

        return $this->map->get($name);
    }



    function exists(string $name): bool {
        return $this->map->exists($name);
    }
}