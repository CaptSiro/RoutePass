<?php
  
  require_once __DIR__ . "/WriteRegistry.php";
  require_once __DIR__ . "/Cookie.php";

  class Request {
    static function POST ($url, array $post = NULL, array $options = []) {
      $defaults = [
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
      ];
    
      $chandler = curl_init();
      curl_setopt_array($chandler, ($options + $defaults));
      if (!$result = curl_exec($chandler)) {
        trigger_error(curl_error($chandler));
      }
      curl_close($chandler);
      return $result;
    }
    static function GET ($url, array $get = NULL, array $options = []) {
      $defaults = [
        CURLOPT_URL => $url . ((strpos($url, '?') === FALSE) ? '?' : '') . http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4
      ];
    
      $chandler = curl_init();
      curl_setopt_array($chandler, ($options + $defaults));
      if (!$result = curl_exec($chandler)){
        trigger_error(curl_error($chandler));
      }
      curl_close($chandler);
      return $result;
    }
    
    public $httpMethod,
      $host,
      $uri,
      $fullURI,
      $response,
      $domain,
      $query,
      $param,
      /** @var RequestRegistry|string $body */
      $body,
      $session,
      $cookies;
    private $headers;
    public function getHeader ($header) {
      return $this->headers[strtolower($header)];
    }
    
    public function __construct (Response &$response) {
      $this->response = $response;
      $this->httpMethod = $_SERVER["REQUEST_METHOD"];
      $this->host = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
          ? "https"
          : "http")
        . "://" . $_SERVER['HTTP_HOST'];
      $this->uri = $_SERVER["REQUEST_URI"];
      $this->fullURI = "$this->host$this->uri";
  
      $temp = apache_request_headers();
      array_walk($temp, function ($value, $key) {
        $this->headers[strtolower($key)] = $value;
      });
      
      //TODO implement body -> HomeRouter::acceptType()
      $this->body = "body";
  
      if (session_status() == PHP_SESSION_NONE) {
        session_start();
      }
  
      $this->session = new WriteRegistry($this, function ($propertyName, $value) {
        $_SESSION[$propertyName] = $value;
        return $value;
      });
      $this->session->load($_SESSION);
      
      $this->cookies = new WriteRegistry($this, function ($propertyName, $value) {
        $cookie = $value;
  
        if (!$cookie instanceof Cookie) throw new Exception('Received value is not instance of Cookie.');
        
        $cookie->set($propertyName);
        return $cookie->value;
      });
      $this->cookies->enableSerializedValues();
      $this->cookies->load($_COOKIE);
      
      $this->param = new RequestRegistry($this);
      $this->domain = new RequestRegistry($this);
      $this->query = new RequestRegistry($this);
    }
  
    public function trimQueries () {
      $uri = $_SERVER["REQUEST_URI"];
      $_SERVER["REQUEST_PATH"] = $uri;
    
      $name = "";
      $value = "";
      $swap = false;
      $contains = false;
    
      for ($i = 0; $i < strlen($uri); $i++) {
        if ($uri[$i] == "?" || $contains) {
          if (!$contains) {
            $_SERVER["REQUEST_PATH"] = substr($uri, 0, $i);
            $_SERVER["QUERY_STRING"] = substr($uri, $i);
            $contains = true;
            continue;
          }
        
          if ($uri[$i] == "=") {
            $swap = true;
            continue;
          }
        
          if ($uri[$i] == "&") {
            $this->query->set($name, $value);
            $name = "";
            $value = "";
            $swap = false;
            continue;
          }
        
          ${$swap ? "value" : "name"} .= $uri[$i];
        }
      }
    
      if ($name != "") {
        $this->query->set($name, $value);
      }
    }
  }