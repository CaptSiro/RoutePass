<?php

  require_once __DIR__ . "/Node.php";
  require_once __DIR__ . "/../../retval/retval.php";

  class PathNode implements Node {
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
    public $parent;
    public function getParent (): ?Node {
      return $this->parent;
    }
    public function setParent(?Node $parent) {
      $this->parent = $parent;
    }
  
    public $pathPart;
    public function getPathPart (): string{
      return $this->pathPart;
    }
    public function setPathPart(string $part) {
      $this->pathPart = $part;
    }
  
  
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

    
    
    public function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      if (empty($uriParts)) {
        $this->handles[$httpMethod] = $callbacks;
        return;
      }

      $part = array_shift($uriParts);

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
    
    
    
    public function setMethod (string &$httpMethod, array &$callbacks) {
      $this->handles[$httpMethod] = $callbacks;
    }
  
    
  
    public function execute (array &$uri, Request &$request, Response &$response) {
      if (empty($uri)) {
        if (isset($this->handles[$_SERVER["REQUEST_METHOD"]])) {
          $doNext = false;
          $nextFunc = function () use (&$doNext) { $doNext = true; };

          foreach ($this->handles[$_SERVER["REQUEST_METHOD"]] as $cb) {
            $cb($request, $response, $nextFunc);

            if ($doNext) {
              $doNext = false;
              continue;
            }
            
            break;
          }
          return;
        }

        var_dump($this->handles);
        exit("$_SERVER[REQUEST_METHOD] is not implemented.");
      }

      $part = array_shift($uri);
      if (isset($this->static[$part])) {
        $this->static[$part]->execute($uri, $request, $response);
        return;
      }

      // breaking chars [-.~]
      foreach ($this->parametric as $regex => $node) {
        if (preg_match($regex, $part, $matches)) {
          if (!isset($request->param)) {
            $request->param = new stdClass();
          }
          
          if ($node instanceof Router) {
            $node = $node->home;
          }
          
          foreach ($node->paramDictionary as $key => $param) {
            $paramLength = strlen($param);
            if ($param[$paramLength - 2] == "[" && $param[$paramLength - 1] == "]") {
              $shortHand = substr($param, 0, -2);
              
              if (isset($request->param->$shortHand)) {
                $request->param->$shortHand[] = $matches[$key];
              } else {
                $request->param->$shortHand = [$matches[$key]];
              }
              continue;
            }
            
            $request->param->$param = $matches[$key];
          }
          $node->execute($uri, $request, $response);
          exit;
        }
      }

      exit("Endpoint do not exist.");
    }
  }

  require_once __DIR__ . "/ParametricPathNode.php";