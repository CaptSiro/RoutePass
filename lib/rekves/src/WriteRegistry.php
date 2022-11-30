<?php

  require_once(__DIR__ . "/RequestRegistry.php");

  class WriteRegistry extends RequestRegistry {
    private $writeFN;

    function __construct (Request $request, Closure $fn) {
      parent::__construct($request);
      $this->writeFN = $fn;
    }

    protected function setValue ($propName, $value) {
      return $this->writeFN->__invoke($propName, $value);
    }
  }