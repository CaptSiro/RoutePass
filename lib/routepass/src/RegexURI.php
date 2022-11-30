<?php

  class RegexURI {
    public static function param (string $name, string $regex) {

    }

    public static function formatParams (string $format, array $bindings) {

    }

    public static function query (array $bindings) {

    }

    // /foo -> string
    // /:bar -> RegexURI::param(name, /regex/)
    // /:foo-:bar -> RegexURI::formatParams(format, [name -> /regex/, name => /regex/]) -> _ is non breaking character but any other will break naming /:bar-foo => /{parameter named bar}-foo; /:bar_foo => /{parameter named bar_foo}
    // /?foo=0&bar=1 -> RegexURI::query([name => /regex/, name => /regex/], ...)
    // storing each /part
    // /0/1/2/3 -> "0", "1", "2", "3"
    public function __construct (...$patterns) {
      
    }
  }

?>