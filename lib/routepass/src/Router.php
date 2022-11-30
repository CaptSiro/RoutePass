<?php

  require_once __DIR__ . "/../../load-env.php";
  require_once __DIR__ . "/PathNode.php";
  require_once __DIR__ . "/INode.php";
  require_once __DIR__ . "/../../rekves/src/Request.php";
  require_once __DIR__ . "/../../rekves/src/Response.php";

  class Router implements INode {
    public static function strictParams (string $uri, array $paramMap) {

    }

    private $home;

    public function __construct () {
      $this->home = new PathNode();
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
      global $env;

      $res = new Response();
      $req = new Request($res);
      $req->query = self::trimQueries();

      $uri = self::filterEmpty(explode("/", substr($_SERVER["REQUEST_PATH"], strlen($env->HOME_DIR))));

      $this->home->execute($uri, $req, $res);
    }

    public function use (string $uri, INode $node) {}

    public function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      if (empty($uriParts)) {
        $this->home->handles[$httpMethod] = $callbacks;
        return;
      }

      $this->home->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
    }

    function execute (array &$uri, Request &$req, Response &$res) {
      $this->home->execute($uri, $req, $res);
    }

    public function createPath (array $uriParts): INode {
      return new PathNode();
    }




    public function for (array $httpMethods, string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      foreach ($httpMethods as $method) {
        $this->assign(strtoupper($method), explode("/", $uriPattern), $callbacks);
      }
    }

    public function forAll (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      foreach (["GET", "HEAD", "POST", "PUT", "DELETE", "CONNECT", "OPTIONS", "TRACE", "PATCH"] as $method) {
        $this->assign(strtoupper($method), explode("/", $uriPattern), $callbacks);
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
?>