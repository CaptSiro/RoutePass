<?php

namespace RoutePass;

function strpos_all(string $needle, string $haystack, int $offset): array {
    $occurrences = [];

    while (($pos = strpos($haystack, $needle, $offset)) !== false) {
        $occurrences[] = $pos;
        $offset = $pos + 1;
    }

    return $occurrences;
}