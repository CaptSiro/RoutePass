<?php

namespace RoutePass\map\descriptors;

interface Write {
    function write(string $name, mixed $value): void;
}