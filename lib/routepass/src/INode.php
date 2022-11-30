<?php

  interface INode {
    /**
     * Returns last node responsible for handling requests.
     */
    public function createPath (array $uriParts): INode;
    /**
     * Assigns callbacks on last node of set path. 
     */
    function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []);
    function execute (array &$uri, Request &$req, Response &$res);
  }