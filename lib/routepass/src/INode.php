<?php

  interface INode {
    /**
     * Returns last node responsible for handling requests.
     */
    function createPath (array $uriParts, array &$paramCaptureGroupMap = []): INode;
    /**
     * Assigns callbacks on last node of set path. 
     */
    function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []);
    function setMethod (string &$httpMethod, array &$callbacks);
    function execute (array &$uri, Request &$req, Response &$res);
  }