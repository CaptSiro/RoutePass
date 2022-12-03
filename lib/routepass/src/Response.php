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
  
    static function propNotFound () {
      return function (string $httpMethod, string $propertyName) {
        $response = new Response();
        $response->setStatusCode(Response::NOT_FOUND);
        $response->error("$propertyName is required for this operation. (method: $httpMethod)");
      };
    }
    
    
    private $headers = [];
    public function hasHeader (string $header) {
      return isset($this->headers[$header]);
    }
    public function setHeader (string $header, string $value) {
      $this->headers[$header] = $value;
    }
    public function setAllHeaders (array ...$headers) {
      foreach ($headers as $header) {
        $this->headers[$header[0]] = $header[1];
      }
    }
    public function removeHeader (string $header) {
      unset($this->headers[$header]);
    }
    public function removeAllHeaders () {
      $this->headers = [];
    }
  
    public function setStatusCode (int $code) {
      http_response_code($code);
    }
    public function generateHeaders () {
      foreach ($this->headers as $header => $value) {
        header("$header: $value");
      }
    }
  
  
    /**
     * Exits the execution without sending any data but headers will be sent.
     */
    public function flush () {
      $this->generateHeaders();
      exit;
    }
    /**
     * Exits the execution.
     *
     * Sends string data to user.
     */
    public function send ($text) {
      $this->generateHeaders();
      exit($text);
    }
    /**
     * Exits the execution.
     *
     * Parses object into JSON text representation and sends it to the user.
     */
    public function json ($jsonEncodeAble, $jsonEncodeFlags = 0, $jsonEncodeDepth = 512) {
      $this->generateHeaders();
      exit(json_encode($jsonEncodeAble, $jsonEncodeFlags, $jsonEncodeDepth));
    }
    /**
     * Exits the execution with error code and message.
     */
    public function error (string $message, int $httpStatusCode = -1) {
      if ($httpStatusCode !== -1) {
        $this->setStatusCode($httpStatusCode);
      }
  
      $this->send($message);
    }
    /**
     * Exits the execution.
     *
     * Reads file and sends it contents to the user.
     *
     * **This function does not download the file on user's end. It only sends file's contents.**
     */
    public function readFile (string $file) {
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
     * Checks for valid file path and sets headers to download it.
     */
    public function download (string $file) {
      $this->setAllHeaders(
        ["Content-Description", "File Transfer"],
        ["Content-Type", 'application/octet-stream'],
        ["Content-Disposition", "attachment; filename=" . basename($file)],
        ["Pregma", "public"],
        ["Content-Length", filesize($file)]
      );
      $this->readFile($file);
    }
    
    public function render (string $view, $locals = [], $doFlushResponse = true) {
      $viewFile = ($_SERVER["VIEW_DIR"] ?? $_SERVER["HOME_DIR"]) . "/$view.php";
      if (!file_exists($viewFile)) {
        $this->setStatusCode(self::NOT_FOUND);
        $this->error("Could not find view: $viewFile");
      }
      
      $predefined = [];
      foreach ($locals as $name => $value) {
        if (isset($$name)) {
          $predefined[$name] = $value;
        }
        
        $$name = $value;
      }
      
      require $viewFile;
      
      foreach ($locals as $name => $value) {
        unset($$name);
      }
      
      foreach ($predefined as $name => $value) {
        $$name = $value;
      }
      
      if ($doFlushResponse) {
        $this->flush();
      }
    }
  }