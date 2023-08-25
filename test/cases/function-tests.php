<?php

use function RoutePass\strpos_all;
use function sptf\functions\expect;
use function sptf\functions\test;

test("finds all occurrences using strpos_all", function () {
    $needle = "pos";
    $haystack = "pos       pos  pos       pos";

    $positions = strpos_all($needle, $haystack, 0);

    expect(count($positions))->toBe(4);

    foreach ($positions as $position) {
        expect(substr($haystack, $position, strlen($needle)))->toBe($needle);
    }
});