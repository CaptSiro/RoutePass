<?php

  require_once __DIR__ . "/Node.php";
  require_once __DIR__ . "/../../retval/retval.php";

  class PathNode extends Node {
    // breaking chars -.~
    // dict
    //   id => "([0-9]+)"
    //   name => "([a-z_]+)"
    public static function createParamFormat (string $uriPart, array $paramCaptureGroupMap = []): array {
      $dict = [];
      $dictI = 1;

      $format = "/^";
      $param = "";
      $registerParam = function () use (&$format, &$param, &$paramCaptureGroupMap, &$dict, &$dictI) {
        if ($param !== "") {
          $format .= $paramCaptureGroupMap[$param] ?? "([^-.~]+)";
          $dict[$dictI++] = $param;
          $param = "";
        }
      };
      $doAppendToParam = false;
      for ($i = 0; $i < strlen($uriPart); $i++) {
        if ($uriPart[$i] == "-" || $uriPart[$i] == "." || $uriPart[$i] == "~" || $uriPart[$i] == "\\" || $uriPart[$i] == "[") {
          if ($uriPart[$i] == "[" && (isset($uriPart[$i + 1]) && $uriPart[$i + 1] == "]")) {
            if ($param !== "") {
              $format .= $paramCaptureGroupMap[$param] ?? "([^-.~]+)";
              $dict[$dictI++] = $param . "[]";
              $param = "";
            }
            
            $i++;
            continue;
          }
          
          $registerParam();

          if (($uriPart[$i] != "\\")) {
            $format .= $uriPart[$i];
          }
          $doAppendToParam = false;
          continue;
        }

        if ($uriPart[$i] == ":") {
          $registerParam();
          $doAppendToParam = true;
          continue;
        }

        ${$doAppendToParam ? "param" : "format"} .= $uriPart[$i];
      }
  
      $registerParam();
      $format .= "$/";

      return [$format, $dict];
    }
  
  
    
    /**
     * @var Node[]
     */
    public $static = [];
    /**
     * @var ParametricPathNode[]|Node[]
     */
    public $parametric = [];
    /**
     * @var Closure[][]
     */
    public $handles = [];
    private $redirectAllToHome = false;
  
  
    public function __construct (string $pathPart, Node $parent) {
      $this->parent = $parent;
      $this->pathPart = $pathPart;
    }
  
  
    
    public function createPath (array $uriParts, array &$paramCaptureGroupMap = []): Node {
      if (empty($uriParts)) {
        return $this;
      }
  
      $part = array_shift($uriParts);
  
      if (isset($this->static[$part])) {
        return $this->static[$part]->createPath($uriParts, $paramCaptureGroupMap);
      }
  
      [$regex, $dict] = self::createParamFormat($part, $paramCaptureGroupMap);
      if (isset($this->parametric[$regex])) {
        return $this->parametric[$regex]->createPath($uriParts, $paramCaptureGroupMap);
      }
  
      //* create new end point
      if (strpos($part, ":") === false) {
        //* static
        $node = new PathNode($part, $this);
        $this->static[$part] = $node;
        return $node->createPath($uriParts, $paramCaptureGroupMap);
      }
  
      //* parametric
      $node = new ParametricPathNode($regex, $this);
      $this->parametric[$regex] = $node;
      $node->paramDictionary = $dict;
      return $node->createPath($uriParts, $paramCaptureGroupMap);
    }

    
    
    protected function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      if (empty($uriParts)) {
        $this->handles[$httpMethod] = $callbacks;
        return;
      }

      $part = array_shift($uriParts);
      if ($part[0] == "*" && strlen($part) == 1) {
        $this->redirectAllToHome = true;
        $this->handles[$httpMethod] = $callbacks;
        return;
      }

      if (isset($this->static[$part])) {
        $this->static[$part]->assign($httpMethod, $uriParts, $callbacks);
        return;
      }

      [$regex, $dict] = self::createParamFormat($part, $paramCaptureGroupMap);
      if (isset($this->parametric[$regex])) {
        $this->parametric[$regex]->assign($httpMethod, $uriParts, $callbacks);
        return;
      }
      
      //* create new end point
      
      if (strpos($part, ":") === false) {
        //* static
        $node = new PathNode($part, $this);
        $node->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
        $this->static[$part] = $node;
        return;
      }

      //* parametric
      $node = new ParametricPathNode($regex, $this);
      $node->assign($httpMethod, $uriParts, $callbacks);
      $this->parametric[$regex] = $node;
      $node->paramDictionary = $dict;
    }
    
    
    
    protected function setMethod (string &$httpMethod, array &$callbacks) {
      $this->handles[$httpMethod] = $callbacks;
    }
  
    
  
    protected function execute (array &$uri, Request &$request, Response &$response) {
      if (empty($uri)) {
        $this->callHandlesClosures($request, $response);
      }

      $part = array_shift($uri);
      if (isset($this->static[$part])) {
        $this->static[$part]->execute($uri, $request, $response);
        return;
      }

      // breaking chars [-.~]
      foreach ($this->parametric as $regex => $node) {
        if (preg_match($regex, $part, $matches)) {
          if ($node instanceof Router) {
            $node = $node->home;
          }
          
          foreach ($node->paramDictionary as $key => $param) {
            $paramLength = strlen($param);
            if ($param[$paramLength - 2] == "[" && $param[$paramLength - 1] == "]") {
              $shortHand = substr($param, 0, -2);
              
              if ($request->param->isset($shortHand)) {
                $request->param->modify($shortHand, function ($value) use ($matches, $key) {
                  $value[] = $matches[$key];
                  return $value;
                });
              } else {
                $request->param->set($shortHand, [$matches[$key]]);
              }
              continue;
            }
            
            $request->param->set($param, $matches[$key]);
          }
          $node->execute($uri, $request, $response);
          exit;
        }
      }
      
      if ($this->redirectAllToHome) {
        $request->remainingURI = "$part" . (count($uri) == 0 ? "" : ("/" . join("/", $uri)));
        $this->callHandlesClosures($request, $response);
      }
  
      $request->homeRouter->endpointDoesNotExists($request, $response);
    }
    
    
    
    private function callHandlesClosures (Request $request, Response $response) {
      if (isset($this->handles[$_SERVER["REQUEST_METHOD"]])) {
        $doNext = false;
        $nextFunc = function () use (&$doNext) { $doNext = true; };
    
        foreach ($this->handles[$_SERVER["REQUEST_METHOD"]] as $cb) {
          $cb($request, $response, $nextFunc);
      
          if ($doNext) {
            $doNext = false;
            continue;
          }
      
          if ($request->homeRouter->getFlag(HomeRouter::FLAG_RESPONSE_AUTO_FLUSH)) {
            $response->flush();
          }
          
          break;
        }
        return;
      }
  
      $request->homeRouter->httpMethodNotImplemented($request, $response);
    }
  }

  require_once __DIR__ . "/ParametricPathNode.php";