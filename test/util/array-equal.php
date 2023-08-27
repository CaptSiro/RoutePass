<?php

function array_equal(array $a, array $b): bool {
    $c = count($a);

    if ($c !== count($b)) {
        return false;
    }

    $n = 0;

    foreach ($a as $key => $item) {
        $n++;

        if ($b[$key] !== $item) {
            return false;
        }
    }

    return $n === $c;
}