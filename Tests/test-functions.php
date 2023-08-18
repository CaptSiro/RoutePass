<?php

function setServerVars(array $props): Closure {
    $copy = [...$_SERVER];

    foreach ($props as $name => $value) {
        $_SERVER[$name] = $value;
    }

    return fn() => $_SERVER = $copy;
}