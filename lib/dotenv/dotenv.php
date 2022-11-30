<?php

  require_once __DIR__ . "/../retval/retval.php";

  class Env {
    private static function parseLine ($line) {
      $matches = [$line];
      $group = "";
      for ($c = 0; $c < strlen($line); $c++) { 
        if ($line[$c] == "=" || $line[$c] == "#") {
          $matches[] = trim($group, "\r\n ");
          $group = "";
          continue;
        }

        $group .= $line[$c];
      }

      $matches[] = trim($group, "\r\n ");

      return $matches;
    }

    private $__map = [];
    function __construct ($file) {
      $this->__map["__ENV_FILE__"] = $file;

      $handle = fopen($file, "r");
      if ($handle) {
        while (($line = fgets($handle)) !== false) {
          $matches = self::parseLine($line);
          $this->__map[$matches[1]] = $matches[2];
        }

        fclose($handle);
      }
    }

    public function get ($name): Result {
      if (!isset($this->__map[$name])) {
        return fail(new NotFoundExc("Could not find $name in " . $this->__map["__ENV_FILE__"]));
      }
  
      return success($this->__map[$name]);
    }
    
    function __get ($name) {
      if (!isset($this->__map[$name])) {
        return null;
      }
  
      return $this->__map[$name];
    }
  }