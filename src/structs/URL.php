<?php

namespace RoutePass\structs;

use RoutePass\map\StrictMap;
use RoutePass\RoutePass;
use RoutePass\RoutePassError;

readonly class URL {
    function __construct(
        private string $protocol,
        private string $host,
        private string $path,
        private string $queryString,
        public StrictMap $query
    ) {}



    /**
     * @return string
     */
    public function getHost(): string {
        return $this->host;
    }



    /**
     * @return string
     */
    public function getPath(): string {
        return $this->path;
    }



    /**
     * @return string
     */
    public function getQueryString(): string {
        return $this->queryString;
    }



    /**
     * @return string
     */
    public function getProtocol(): string {
        return $this->protocol;
    }



    static function request(): self {
        $path = $_SERVER['REQUEST_URI'];
        $hostStart = strpos($path, $_SERVER['HTTP_HOST']);

        if ($hostStart !== false) {
            $path = substr($path, $hostStart + strlen($_SERVER['HTTP_HOST']));
        }

        $queryStart = strpos($path, "?");

        if ($queryStart !== false) {
            $path = substr($path, 0, $queryStart);
        }

        return new self(
            $_SERVER['REQUEST_SCHEME'],
            $_SERVER['HTTP_HOST'],
            $path,
            $_SERVER['QUERY_STRING'],
            new StrictMap(
                fn (string $name) => RoutePass::throwError(RoutePassError::PROP_NOT_FOUND, "Could not find '$name' in query"),
                $_GET
            )
        );
    }
}