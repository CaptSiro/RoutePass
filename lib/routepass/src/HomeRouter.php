<?php
  require_once __DIR__ . "/Router.php";
  require_once __DIR__ . "/../../load-env.php";
  require_once __DIR__ . "/Response.php";
  require_once __DIR__ . "/Request.php";
  
  class HomeRouter extends Router {
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
      
      $this->onErrorEvent(function ($message) {
        exit($message);
      });
    }
  
    
    
    // [domain].host
    // static.host
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
      $home = "";
      $dir = dirname($_SERVER["SCRIPT_FILENAME"]);
    
      for ($i = 0; $i < strlen($dir); $i++) {
        if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
          $home .= $dir[$i];
        }
      }
      
      $_SERVER["HOME_DIR"] = $dir;
      
      $res = new Response();
      $req = new Request($res);
    
      $req->trimQueries();
      $uri = self::filterEmpty(explode("/", substr($_SERVER["REQUEST_PATH"], strlen($home))));
      
      if (isset($this->staticDomains[$_SERVER["HTTP_HOST"]])) {
        $this->staticDomains[$_SERVER["HTTP_HOST"]]->execute($uri, $req, $res);
        exit;
      }
      
      foreach ($this->parametricDomains as $regex => $domainRouter) {
        if (preg_match($regex, $_SERVER["HTTP_HOST"], $matches)) {
          foreach ($domainRouter->domainDictionary as $key => $domain) {
            $req->domain->set($domain, $matches[$key]);
          }
    
          $domainRouter->execute($uri, $req, $res);
          exit;
        }
      }
      
      $this->home->execute($uri, $req, $res);
    }
    
    
    private $httpMethodNotImplementedHandler;
    private $endpointDoesNotExistsHandler;
    
    public function onHTTPMethodNotImplemented (Closure $handler) {
      $this->httpMethodNotImplementedHandler = $handler;
    }
    public function onEndpointDoesNotExists (Closure $handler) {
      $this->endpointDoesNotExistsHandler = $handler;
    }
    public function onErrorEvent (Closure $handler) {
      $this->onHTTPMethodNotImplemented($handler);
      $this->onEndpointDoesNotExists($handler);
    }
    public function httpMethodNotImplemented (Request $request, Response $response) {
      $this->httpMethodNotImplementedHandler->call($this, "HTTP method: '$_SERVER[REQUEST_METHOD]' is not implemented for '$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]'", $request, $response);
      exit;
    }
    public function endpointDoesNotExists (Request $request, Response $response) {
      $this->endpointDoesNotExistsHandler->call($this, "Endpoint does not exist for '$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]'", $request, $response);
      exit;
    }
    
    public function setViewDirectory ($directory) {
      $_SERVER["VIEW_DIR"] = $directory;
    }
  }