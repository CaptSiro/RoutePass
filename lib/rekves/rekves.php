<?php

  require_once(__DIR__ . "/src/Response.php");
  require_once(__DIR__ . "/src/Request.php");
  require_once(__DIR__ . "/src/Cookie.php");

  $res = new Response();
  $req = new Request($res);