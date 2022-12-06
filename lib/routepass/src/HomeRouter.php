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
      $this->setBodyParser(self::BODY_PARSER_URLENCODED());
    }
  
  
    /**
     * Set Router object to specific domain format.
     *
     * ### Domain format
     *
     * Static domains
     * - users.example.com
     *
     * Dynamic domains
     * - [mySubDomain].example.com
     * - mySubDomain is accessible within `$request->domain->get("mySubDomain")`
     * - Regular expression can be added to dynamic domain parameter by passing map of keys (names of domains) and values (regex capture group /(0-9)/)
     * @param string $domainPattern
     * @param Router $router
     * @param array $domainCaptureGroupMap
     * @return void
     */
    public function domain (string $domainPattern, Router $router, array $domainCaptureGroupMap = []) {
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
    public function static (string $urlPattern, string $absoluteDirectoryPath, array $paramCaptureGroupMap = []) {
      $staticRouter = new Router();
      $staticRouter->get("/*", [function (Request $request, Response $response) use ($absoluteDirectoryPath) {
        $filePath = "$absoluteDirectoryPath/$request->remainingURI";
        if (is_dir($filePath)) {
          $basenameMapper = function ($item) {
            return basename($item);
          };
          
          $files = glob("$filePath/*.*");
          $directories = glob("$filePath/*/");
          
          $path = explode("/", $request->remainingURI);
          
          $response->renderFile(__DIR__ . "/directory-walker.php", [
            "home" => $absoluteDirectoryPath,
            "path" => $path[0] !== "" ? $path : [],
            "files" => array_map($basenameMapper, $files),
            "directories" => array_map($basenameMapper, $directories)
          ]);
        }
        $response->setHeader("Content-Type", mime_content_type($filePath));
        $response->readFile($filePath);
      }], ["filePath" => Router::REGEX_ANY]);
      
      parent::use($urlPattern, $staticRouter, $paramCaptureGroupMap);
    }
    public function serve () {
      $home = "";
      $dir = dirname($_SERVER["SCRIPT_FILENAME"]);
    
      for ($i = 0; $i < strlen($dir); $i++) {
        if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
          $home .= $dir[$i];
        }
      }
      
      $_SERVER["HOME_DIR"] = $home;
      $_SERVER["HOME_DIR_PATH"] = $dir;
      
      $res = new Response();
      $req = new Request($res, $this);
      $this->bodyParser->call($this, file_get_contents('php://input'), $req);
    
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
    private $propertyNotFoundHandler;
    
    public function onHTTPMethodNotImplemented (Closure $handler) {
      $this->httpMethodNotImplementedHandler = $handler;
    }
    public function onEndpointDoesNotExists (Closure $handler) {
      $this->endpointDoesNotExistsHandler = $handler;
    }
    public function onPropertyNotFound (Closure $handler) {
      $this->propertyNotFoundHandler = $handler;
    }
    public function onErrorEvent (Closure $handler) {
      $this->onHTTPMethodNotImplemented($handler);
      $this->onEndpointDoesNotExists($handler);
      $this->onPropertyNotFound($handler);
    }
    public function httpMethodNotImplemented (Request $request, Response $response) {
      $this->httpMethodNotImplementedHandler->call($this, "HTTP method: '$_SERVER[REQUEST_METHOD]' is not implemented for '$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]'", $request, $response);
      exit;
    }
    public function endpointDoesNotExists (Request $request, Response $response) {
      $this->endpointDoesNotExistsHandler->call($this, "Endpoint does not exist for '$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]'", $request, $response);
      exit;
    }
    public function propertyNotFound (string $message, Request $request, Response $response) {
      $this->propertyNotFoundHandler->call($this, $message, $request, $response);
      exit;
    }
    
    
    public function setViewDirectory ($directory) {
      $_SERVER["VIEW_DIR"] = $directory;
    }
    
    private $flags = [
      self::FLAG_RESPONSE_AUTO_FLUSH => true
    ];
    
    
    /**
     * When callback is called and next function is not called the response will be automatically sent.
     *
     * Expected value type: boolean
     *
     * Default: true
     */
    public const FLAG_RESPONSE_AUTO_FLUSH = "DO_RESPONSE_AUTO_FLUSH";
    
    
    public function setFlag ($flag, $value) {
      $this->flags[$flag] = $value;
    }
    public function getFlag ($flag) {
      return $this->flags[$flag] ?: null;
    }
  
    /**
     * @var Closure $bodyParser
     */
    private $bodyParser;
    public function setBodyParser (Closure $bodyParser) {
      $this->bodyParser = $bodyParser;
    }
  
    /**
     * Parses body as a json object {}, if the main object is array the body will be that array even if `$convertToRegistry` is set to true.
     *
     * RequestFile upload is only accessible with HTTP POST method and Content-Type: "multipart/form-data" thus when this header is set, the body will automatically become Register object with set values.
     *
     * If array is sent, it is stored under "array" property on body object, use this to access it: `$request->body->get("array")`
     * @return Closure
     */
    public static function BODY_PARSER_JSON () {
      return function ($bodyContents, Request $request) {
        $request->body = new RequestRegistry($request);

        if (!(strpos($request->getHeader("Content-Type"), "multipart/form-data") === false)) {
          $request->body->load($_POST);
          return;
        }
  
        $json = json_decode($bodyContents);
        if (is_array($json)) {
          $request->body->set("array", $json);
          return;
        }
        
        if ($json !== null) {
          foreach ($json as $key => $value) {
            $request->body->set($key, $value);
          }
        }
      };
    }
  
    /**
     * Parses body as text. Stored under "text" property on body object, use this to access it: `$request->body->get("text")`
     * @return Closure
     */
    public static function BODY_PARSER_TEXT () {
      return function ($bodyContents, Request $request) {
        $request->body = new RequestRegistry($request);
  
        if (!(strpos($request->getHeader("Content-Type"), "multipart/form-data") === false) && !empty($_POST)) {
          foreach ($_POST as $key => $value) {
            $bodyContents .= urlencode($key) . "=" . urlencode($value) . "&";
          }
          $bodyContents = substr($bodyContents, 0, -1);
        }
        
        $request->body->set("text", $bodyContents);
      };
    }
  
    /**
     * Parses body as urlencoded string and its entries are stored as properties on body object.
     *
     * If Content-Type header contains multipart/form-data (file upload) the remaining entries will be parsed as urlencoded string. You may use `Request::parseURLEncoded($request->body->get("text"), $request->body)` to populate the `$request->body` registry with key-value pairs.
     * @return Closure
     */
    public static function BODY_PARSER_URLENCODED () {
      return function ($bodyContents, Request $request) {
        $request->body = new RequestRegistry($request);
        
        if (!(strpos($request->getHeader("Content-Type"), "multipart/form-data") === false)) {
          $request->body->load($_POST);
          return;
        }
        
        Request::parseURLEncoded($bodyContents, $request->body);
      };
    }
  }