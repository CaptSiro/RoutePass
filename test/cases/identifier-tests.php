<?php

use RoutePass\tree\path\parser\Ident;
use function sptf\functions\test;
use function sptf\functions\fail;



test("parse valid identifiers", function () {
    $pass = true;
    $idents = ["_", "a", "A", "_asdf_ASDF_1234", "_1", "name"];

    foreach ($idents as $ident) {
        if (!Ident::validate($ident)) {
            fail("Should parse: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        pass();
    }
});



test("find invalid identifiers", function () {
    $pass = true;
    $idents = ["", "1", "foo-bar", "foo!", "你好"];

    foreach ($idents as $ident) {
        if (Ident::validate($ident)) {
            fail("Should invalidate: '$ident'");
            $pass = false;
        }
    }

    if ($pass) {
        pass();
    }
});