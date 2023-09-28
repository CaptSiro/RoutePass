<?php

namespace RoutePass\map\descriptors;

interface Exists {
    function exists(string $name): bool;
}