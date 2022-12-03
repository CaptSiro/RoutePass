<?php

  require_once __DIR__ . "/PathNode.php";
  require_once __DIR__ . "/Node.php";
  require_once __DIR__ . "/../../rekves/src/Request.php";
  require_once __DIR__ . "/../../rekves/src/Response.php";

  class Router extends Node {
    /** Try to avoid using as much as possible. May cause problems with `'internal param break character'` (characters that are not considered as valid param name. `/user/:user-id` interpreted as `/user/{space for 'user' parameter}-id`) */
    const REG_ANY = "(.*)";
    const REG_NUMBER = "([0-9]+)";
    const REG_WORD = "([a-zA-Z]+)";
    const REG_WORD_UPPER = "([A-Z]+)";
    const REG_WORD_LOWER = "([a-z]+)";
    const REG_SENTENCE = "([a-zA-Z_]+)";
    const REG_SENTENCE_UPPER = "([A-Z_]+)";
    const REG_SENTENCE_LOWER = "([a-z_]+)";
  
    protected static function filterEmpty (array $toBeFiltered) {
      $return = [];
      foreach ($toBeFiltered as $fragment) {
        if ($fragment != "") {
          $return[] = $fragment;
        }
      }
    
      return $return;
    }
  
    public $home;
    /**
     * @var Router[]
     */
    public $domainDictionary = [];
    
    
    public function __construct (Node $parent = null) {
      $this->home = new PathNode("", $this);
      $this->parent = $parent;
    }
    public function use (string $uriPattern, Router $router, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $lastNode = $this->createPath($parsedURI, $paramCaptureGroupMap);
      
      $part = $lastNode->getPathPart();
      $parent = $lastNode->getParent();
      $router->setPathPart($part);
      $router->setParent($parent);
      
      if ($lastNode instanceof ParametricPathNode) {
        $parent->parametric[$part] = $router;
        if (!$router->home instanceof ParametricPathNode) {
          $paramNode = new ParametricPathNode($part, $router);
          $router->home = $paramNode->upgrade($router->home);
        }
        
        $router->home->paramDictionary = $lastNode->paramDictionary;
        return;
      }
      
      $parent->static[$part] = $router;
    }
    protected function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      if (empty($uriParts)) {
        $this->home->handles[$httpMethod] = $callbacks;
        return;
      }

      $this->home->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
    }
    protected function setMethod (string &$httpMethod, array &$callbacks) {
      $this->home->setMethod($httpMethod, $callbacks);
    }
    protected function execute (array &$uri, Request &$req, Response &$response) {
      $this->home->execute($uri, $req, $response);
    }
    public function createPath (array $uriParts, array &$paramCaptureGroupMap = []): Node {
      return $this->home->createPath($uriParts, $paramCaptureGroupMap);
    }




    public function for (array $httpMethods, string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $lastNode = $this->createPath($parsedURI, $paramCaptureGroupMap);
  
      foreach ($httpMethods as $method) {
        $m = strtoupper($method);
        $lastNode->setMethod($m, $callbacks);
      }
    }
    public function forAll (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $lastNode = $this->createPath($parsedURI, $paramCaptureGroupMap);
      
      foreach (["GET", "HEAD", "POST", "PUT", "DELETE", "CONNECT", "OPTIONS", "TRACE", "PATCH"] as $method) {
        $lastNode->setMethod($method, $callbacks);
      }
    }



    public function get (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "GET";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function head (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "HEAD";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function post (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "POST";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function put (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "PUT";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function delete (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "DELETE";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function connect (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "CONNECT";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function options (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "OPTIONS";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function trace (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "TRACE";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
    public function patch (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "PATCH";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  }