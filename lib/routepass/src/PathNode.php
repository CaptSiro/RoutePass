<?php

  require_once __DIR__ . "/INode.php";
  require_once __DIR__ . "/../../retval/retval.php";

  class PathNode implements INode {
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
        if ($uriPart[$i] == "-" || $uriPart[$i] == "." || $uriPart[$i] == "~" || $uriPart[$i] == "\\") {
          $registerParam();

          if ($uriPart[$i] != "\\") {
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

    
    
    public $static = [];
    public $parametric = [];
    public $handles = [];

    
    
    public function createPath (array $uriParts, array &$paramCaptureGroupMap = []): INode {
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
        $node = new PathNode();
        $this->static[$part] = $node;
        return $node->createPath($uriParts, $paramCaptureGroupMap);
      }
  
      //* parametric
      $node = new ParametricPathNode();
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
        $node = new PathNode();
        $node->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
        $this->static[$part] = $node;
        return;
      }

      //* parametric
      $node = new ParametricPathNode();
      $node->assign($httpMethod, $uriParts, $callbacks);
      $this->parametric[$regex] = $node;
      $node->paramDictionary = $dict;
    }
    
    
    
    public function setMethod (string &$httpMethod, array &$callbacks) {
      $this->handles[$httpMethod] = $callbacks;
    }
  
    
  
    public function execute (array &$uri, Request &$req, Response &$res) {
      if (empty($uri)) {
        if (isset($this->handles[$_SERVER["REQUEST_METHOD"]])) {
          $doNext = false;
          $nextFunc = function () use (&$doNext) { $doNext = true; };

          foreach ($this->handles[$_SERVER["REQUEST_METHOD"]] as $cb) {
            $cb($req, $res, $nextFunc);

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
        $this->static[$part]->execute($uri, $req, $res);
        return;
      }

      // breaking chars [-.~]
      foreach ($this->parametric as $regex => $node) {
        if (preg_match($regex, $part, $matches)) {
          $req->param = new stdClass();
          foreach ($node->paramDictionary as $key => $param) {
            $req->param->$param = $matches[$key];
          }
          $node->execute($uri, $req, $res);
          exit;
        }
      }

      exit("End point does not exist.");
    }
  }

  require_once __DIR__ . "/ParametricPathNode.php";