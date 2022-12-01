<?php

  require_once(__DIR__ . "/RequestRegistry.php");
  require_once(__DIR__ . "/WriteRegistry.php");
  require_once __DIR__ . "/Cookie.php";

  class Request {
    static function POST ($url, array $post = NULL, array $options = []) {
      $defaults = array(
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
      );
    
      $chandler = curl_init();
      curl_setopt_array($chandler, ($options + $defaults));
      if (!$result = curl_exec($chandler)) {
        trigger_error(curl_error($chandler));
      }
      curl_close($chandler);
      return $result;
    }

    static function GET ($url, array $get = NULL, array $options = array()) {   
      $defaults = array(
        CURLOPT_URL => $url . ((strpos($url, '?') === FALSE) ? '?' : '') . http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4
      );
       
      $chandler = curl_init();
      curl_setopt_array($chandler, ($options + $defaults));
      if (!$result = curl_exec($chandler)){
        trigger_error(curl_error($chandler));
      }
      curl_close($chandler);
      return $result;
    }



    public $method, $host, $uri, $fullUrl, $gatewayFile, // server-url properties
    $res, // pointer to response object for this request
    $domain, $query, $param, $body, $session, $cookies; // content Registries
    private $headers = [];
    public function getHeader (string $field) {
      return $this->headers[strtolower($field)];
    }

    function __construct (Response &$res) {
      $this->res = $res;


      $this->gatewayFile = basename($_SERVER['SCRIPT_FILENAME']);


      $this->method = $_SERVER['REQUEST_METHOD'];
      $this->host = 
        ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
          ? "https"
          : "http")
        . "://" . $_SERVER['HTTP_HOST'];
      $this->uri = $_SERVER["REQUEST_URI"];
      $this->fullUrl = "$this->host$this->uri";


      $temp = apache_request_headers();
      array_walk($temp, function ($value, $key) {
        $this->headers[strtolower($key)] = $value;
      });


      $this->body = new RequestRegistry($this);
      $this->body->load($_GET, $_POST, $_FILES);


      if (session_status() == PHP_SESSION_NONE) {
        session_start();
      }


      $this->session = new WriteRegistry($this, function ($propName, $value) {
        $_SESSION[$propName] = $value;
        return $value;
      });
      $this->session->load($_SESSION);


      $this->cookies = new WriteRegistry($this, function ($propName, $value) {
        $cookie = $value;

        if (!$cookie instanceof Cookie) throw new Exception('Recieved value is not instance of Cookie.');
        $cookie->set($propName);
        return $cookie->value;
      });
      $this->cookies->enableSerializedValues();
      $this->cookies->load($_COOKIE);
    }
  }