<?php

  function import(string $lib, string $module = null): void {
    if ($module !== null) {
      require_once __DIR__ . "/$lib/$module.php";
      return;
    }
    
    require_once __DIR__ . "/$lib/$lib.php";
  }