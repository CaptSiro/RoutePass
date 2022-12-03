<?php
  
  require_once __DIR__ . "/StrictRegistry.php";
  
  class RequestRegistry extends StrictRegistry {
    private $request;
    
    public function __construct (Request $request) {
      $this->request = $request;
    }
  
    protected function propNotFound($propertyName) {
      $this->request->response->setStatusCode(Response::BAD_REQUEST);
      $this->request->response->error("$propertyName is required for this operation.");
    }
  
    protected function setValue($propertyName, $value) {
      return $value;
    }
  }