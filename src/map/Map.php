<?php

namespace RoutePass\map;

require_once __DIR__ . "/descriptors/Get.php";
require_once __DIR__ . "/descriptors/Write.php";
require_once __DIR__ . "/descriptors/Load.php";
require_once __DIR__ . "/descriptors/Exists.php";

use RoutePass\map\descriptors\Exists;
use RoutePass\map\descriptors\Get;
use RoutePass\map\descriptors\Load;
use RoutePass\map\descriptors\Write;

class Map implements Write, Get, Exists, Load {
    private array $map;



    public function __construct(array $array = []) {
        $this->map = $array;
    }



    function get(string $name, $or = null): mixed {
        return $this->map[$name] ?? $or;
    }



    function write(string $name, mixed $value): void {
        $this->map[$name] = $value;
    }



    function exists(string $name): bool {
        return isset($this->map[$name]);
    }



    function load(array $array): void {
        $this->map = array_merge($this->map, $array);
    }
}