<?php

class Response {
  
  // Informational 
    const CONTINUE = 100;
    const SWITCHING_PROTOCOLS = 101;

  // Successful 
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;

  // Redirection 
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;

  // Client Error
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const PAYLOAD_TOO_LARGE = 413;
    const URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;

  // Server Error
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;

  
  function __construct (array $headers = []) {
    $this->headers = $headers;
  }
  private $headers = [];



  function setHeader (string $hName, string $hValue) {
    $this->headers[$hName] = $hValue;
  }

  function setHeaders (array $headers) {
    foreach ($headers as $header) {
      $this->headers[$header[0]] = $header[1];
    }
  }

  function hasHeader (string $hName) {
    return isset($this->headers[$hName]);
  }

  function removeHeader (string $hName) {
    unset($this->headers[$hName]);
  }

  function setStatusCode (int $code) {
    http_response_code($code);
  }

  function generateHeaders () {
    foreach ($this->headers as $name => $value) {
      header("$name: $value");
    }
  }



  /**
   * Exits the execution.
   * 
   * Sends string data to user.
   */
  function send (string $text) {
    $this->generateHeaders();
    exit($text);
  }

  /**
   * Exits the execution without sending any data but headers will be sent.
   */
  function flush () {
    $this->generateHeaders();
    exit();
  }

  function readFile (string $file) {
    if (!file_exists($file)) {
      $this->setStatusCode(self::NOT_FOUND);
      $this->error("File not found: $file");
    }

    readfile($file);
    exit;
  }
  
  /**
   * Exits the execution.
   * 
   * Parses object into JSON text representation and sends it to the user.
   */
  function json ($jsonEncodeAble) {
    $this->generateHeaders();
    exit(json_encode($jsonEncodeAble));
  }

  /**
   * Exits the execution.
   * 
   * Checks for valid file path and sets headers to download it.
   */
  function download (string $file) {
    if (!file_exists($file)) {
      $this->setStatusCode(self::NOT_FOUND);
      $this->error("File not found: $file");
    }

    $this->setHeaders([
      ["Content-Description", "File Transfer"],
      ["Content-Type", 'application/octet-stream'],
      ["Content-Disposition", "attachment; filename=" . basename($file)],
      ["Pregma", "public"],
      ["Content-Length", filesize($file)]
    ]);

    $this->generateHeaders();
    
    readfile($file);
    exit();
  }


  /**
   * Exits the execution with error code and message.
   */
  function error (string $errorMessage, int $statusCode = -1) {
    if ($statusCode !== -1) {
      $this->setStatusCode($statusCode);
    }

    $this->send($errorMessage);
  }


  
  static function propNotFound () {
    return function (string $HTTPMethod, string $propName) {
      $response = new Response();
      $response->setStatusCode(Response::NOT_FOUND);
      $response->error("$propName is required for this operation. (method: $HTTPMethod)");
    };
  }
}?>