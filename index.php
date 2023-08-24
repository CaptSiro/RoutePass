<?php

if (isset($_GET["__internal-test"])) {
    require __DIR__ . "/test/index.php";
    exit;
}