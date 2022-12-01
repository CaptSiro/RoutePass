<?php

  interface Node {
    /**
     * Returns last node responsible for handling requests.
     */
    function createPath (array $uriParts, array &$paramCaptureGroupMap = []): Node;
    /**
     * Assigns callbacks on last node of set path. 
     */
    function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []);
    function setMethod (string &$httpMethod, array &$callbacks);
    function execute (array &$uri, Request &$request, Response &$response);
    
    function getParent (): ?Node;
    function setParent (?Node $parent);
    function getPathPart (): string;
    function setPathPart (string $part);
  }