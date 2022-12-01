<?php

  require_once __DIR__ . "/../../load-env.php";
  require_once __DIR__ . "/PathNode.php";
  require_once __DIR__ . "/INode.php";
  require_once __DIR__ . "/../../rekves/src/Request.php";
  require_once __DIR__ . "/../../rekves/src/Response.php";

  class Router implements INode {
    /** Try to avoid using as much as possible. May cause problems with `'internal param break character'` (characters that are not considered as valid param name. `/user/:user-id` interpreted as `/user/{space for 'user' parameter}-id`) */
    const REG_ANY = "(.*)";
    const REG_NUMBER = "([0-9]+)";
    const REG_WORD = "([a-zA-Z]+)";
    const REG_WORD_UPPER = "([A-Z]+)";
    const REG_WORD_LOWER = "([a-z]+)";
    const REG_SENTENCE = "([a-zA-Z_]+)";
    const REG_SENTENCE_UPPER = "([A-Z_]+)";
    const REG_SENTENCE_LOWER = "([a-z_]+)";

    public $home;
    private $parent;
    private $pathPart = "";
  
    /**
     * @return INode|null
     */
    public function getParent(): ?INode {
      return $this->parent;
    }
    public function setParent(?INode $parent) {
      $this->parent = $parent;
    }
    
    public function getPathPart(): string {
      return $this->pathPart;
    }
    public function setPathPart(string $part) {
      $this->pathPart = $part;
    }
  
    public function __construct (INode $parent = null) {
      $this->home = new PathNode("", $this);
      $this->parent = $parent;
    }

    private static function filterEmpty (array $toBeFiltered) {
      $return = [];
      foreach ($toBeFiltered as $fragment) {
        if ($fragment != "") {
          $return[] = $fragment;
        }
      }

      return $return;
    }

    private static function trimQueries () {
      $uri = $_SERVER["REQUEST_URI"];
      $_SERVER["REQUEST_PATH"] = $uri;
      $queries = [];

      $name = "";
      $value = "";
      $swap = false;
      $contains = false;

      for ($i = 0; $i < strlen($uri); $i++) {
        if ($uri[$i] == "?" || $contains == true) {
          if ($contains == false) {
            $_SERVER["REQUEST_PATH"] = substr($uri, 0, $i);
            $_SERVER["QUERY_STRING"] = substr($uri, $i);
            $contains = true;
            continue;
          }

          if ($uri[$i] == "=") {
            $swap = true;
            continue;
          }

          if ($uri[$i] == "&") {
            $queries[$name] = $value;
            $name = "";
            $value = "";
            $swap = false;
            continue;
          }

          ${$swap ? "value" : "name"} .= $uri[$i];
        }
      }

      if ($name != "") {
        $queries[$name] = $value;
      }

      return $queries;
    }

    public function serve () {
      $homeDir = "";
      $dir = dirname($_SERVER["SCRIPT_FILENAME"]);

      for ($i = 0; $i < strlen($dir); $i++) {
          if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
              $homeDir .= $dir[$i];
          }
      }

      $res = new Response();
      $req = new Request($res);
      $req->query = self::trimQueries();

      $uri = self::filterEmpty(explode("/", substr($_SERVER["REQUEST_PATH"], strlen($homeDir))));
      $this->home->execute($uri, $req, $res);
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

    public function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      if (empty($uriParts)) {
        $this->home->handles[$httpMethod] = $callbacks;
        return;
      }

      $this->home->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
    }
    
    public function setMethod (string &$httpMethod, array &$callbacks) {
      $this->home->setMethod($httpMethod, $callbacks);
    }
  
    public function execute (array &$uri, Request &$req, Response &$res) {
      $this->home->execute($uri, $req, $res);
    }

    public function createPath (array $uriParts, array &$paramCaptureGroupMap = []): INode {
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