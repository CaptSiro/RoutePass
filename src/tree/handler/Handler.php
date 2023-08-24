<?php

namespace RoutePass\tree\handler;

readonly class Handler {
    private array $handles;

    public function __construct(
        private string $httpMethod
    ) {}



    /**
     * @return string
     */
    public function getHttpMethod(): string {
        return $this->httpMethod;
    }



    function setHandles(array $handles): self {
        $this->handles = $handles;
        return $this;
    }

    function param(string $name, string $pattern): self {
        return $this;
    }

    function query(string $name, string $pattern): self {
        return $this;
    }
}