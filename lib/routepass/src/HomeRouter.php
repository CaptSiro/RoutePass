<?php
  require_once __DIR__ . "/Router.php";
  require_once __DIR__ . "/../../load-env.php";
  
  class HomeRouter extends Router {
    protected static function trimQueries () {
      $uri = $_SERVER["REQUEST_URI"];
      $_SERVER["REQUEST_PATH"] = $uri;
      $query = [];
    
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
            $query[$name] = $value;
            $name = "";
            $value = "";
            $swap = false;
            continue;
          }
        
          ${$swap ? "value" : "name"} .= $uri[$i];
        }
      }
    
      if ($name != "") {
        $query[$name] = $value;
      }
    
      return $query;
    }
    
    private static $instance;
    public static function getInstance (): HomeRouter {
      if (!isset(self::$instance)) {
        self::$instance = new HomeRouter();
      }
      
      return self::$instance;
    }
    
    
    
    /** @var Router[]  */
    protected $parametricDomains = [];
    /** @var Router[]  */
    protected $staticDomains = [];
    public function __construct () {
      parent::__construct();
    }
  
    
    
    // [domain].host
    public function domain (string $domainPattern, Router $router, $domainCaptureGroupMap = []) {
      if (strpos($domainPattern, "[") === false) {
        // static domain
        $this->staticDomains[$domainPattern] = $router;
      } else {
        // parametric domain
        $dictI = 1;
        $dict = [];
  
        $domain = "";
        $format = "/^";
        $registerDomain = function () use (&$format, &$domain, &$domainCaptureGroupMap, &$dict, &$dictI) {
          if ($domain !== "") {
            $format .= $domainCaptureGroupMap[$domain] ?? "([^-.~]+)";
            $dict[$dictI++] = $domain;
            $domain = "";
          }
        };
  
        $doAppendToDomain = false;
        for ($i = 0; $i < strlen($domainPattern); $i++) {
          if ($domainPattern[$i] == "[") {
            $doAppendToDomain = true;
            continue;
          }
    
          if ($domainPattern[$i] == "]") {
            $registerDomain();
            $doAppendToDomain = false;
            continue;
          }
    
          ${$doAppendToDomain ? "domain" : "format"} .= $domainPattern[$i];
        }
  
        $registerDomain();
        $format .= "$/";
  
        $this->parametricDomains[$format] = $router;
        $router->domainDictionary = $dict;
      }
      
      $router->setParent($this);
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
      
      $req->domain = new stdClass();
      if (isset($this->staticDomains[$_SERVER["HTTP_HOST"]])) {
        $this->staticDomains[$_SERVER["HTTP_HOST"]]->execute($uri, $req, $res);
        exit;
      }
      
      foreach ($this->parametricDomains as $regex => $domainRouter) {
        if (preg_match($regex, $_SERVER["HTTP_HOST"], $matches)) {
          foreach ($domainRouter->domainDictionary as $key => $domain) {
            $req->domain->$domain = $matches[$key];
          }
    
          $domainRouter->execute($uri, $req, $res);
          exit;
        }
      }
      
      $this->home->execute($uri, $req, $res);
    }
  }