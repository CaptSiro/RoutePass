<?php

  require_once(__DIR__ . "/StrictRegistry.php");

  class RequestRegistry extends StrictRegistry {
    private $__request;

    function __construct (Request $request) {
      $this->__request = $request;
    }



    protected function propNotFound ($propName) {
      $this->__request->res->setStatusCode(Response::BAD_REQUEST);
      $this->__request->res->error("$propName is required for this operation. (" . self::class . ": " . $this->__request->gatewayFile . ")");
    }

    protected function setValue ($propName, $value) {
      return $value;
    }
  }