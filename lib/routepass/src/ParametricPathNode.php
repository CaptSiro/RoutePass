<?php

  require_once __DIR__ . "/PathNode.php";

  class ParametricPathNode extends PathNode {
    public $paramDictionary = [];
    
    public function upgrade (PathNode $pathNode): ParametricPathNode {
      $this->parent = $pathNode->parent;
      $this->pathPart = $pathNode->pathPart;
      $this->static = $pathNode->static;
      $this->parametric = $pathNode->parametric;
      $this->handles = $pathNode->handles;
      return $this;
    }
  }