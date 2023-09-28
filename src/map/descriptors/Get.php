<?php

namespace RoutePass\map\descriptors;

interface Get {
    function get(string $name, $or = null): mixed;
}