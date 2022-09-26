<?php

namespace System\Http;

class Request
{
    private $method;
    private $url;
    private $query      = [];
    private $attributes = [];
    private $post       = [];
    private $files      = [];
    private $cookies    = [];
    private $headers    = [];
    private $remoteAddress;
    /** @var ?callable */
    private $rawBodyCallback;

    public function __construct(
        string $url,
        array $query = null,
        array $post = null,
        array $attributes = null,
        array $cookies = null,
        array $files = null,
        array $headers = null,
        string $method = null,
        string $remoteAddress = null,
        callable $rawBodyCallback = null
    ) {
        $this->url             = $url;
        $this->query           = $query;
        $this->post            = $post;
        $this->attributes      = $attributes;
        $this->cookies         = $cookies;
        $this->files           = $files;
        $this->headers         = $headers;
        $this->method          = $method ?? 'GET';
        $this->remoteAddress   = $remoteAddress;
        $this->rawBodyCallback = $rawBodyCallback;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getQuery(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->query;
        }

        return $this->query[$key];
    }

    public function getPost(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->post;
        }

        return $this->post[$key] ?? null;
    }

    public function getFile(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->files;
        }

        return $this->files[$key];
    }

    public function getCookie(string $key)
    {
        return $this->cookies[$key] ?? null;
    }

    public function getCookies()
    {
        return $this->cookies;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function isMethod(string $method)
    {
        return strcasecmp($this->method, $method) === 0;
    }

    public function getHeaders(string $header = null)
    {
        if ($header == null) {
            return $this->headers;
        }

        return $this->headers[$header] ?? null;
    }

    public function isHeader(string $header_key, string $header_val)
    {
        if (isset($this->headers[$header_key])) {
            return $this->headers[$header_key] === $header_val;
        }

        return false;
    }

    public function hasHeader(string $header_key)
    {
        return isset($this->headers[$header_key]);
    }

    public function isSecured()
    {
        return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off')
      ? true    // https
      : false;  // http;
    }

    public function getRemoteAddress()
    {
        return $this->remoteAddress;
    }

    public function getRawBody()
    {
        return $this->rawBodyCallback
      ? ($this->rawBodyCallback)()
      : null;
    }

    public function getJsonBody()
    {
        $raw = $this->rawBodyCallback
      ? ($this->rawBodyCallback)()
      : '{}';

        return json_decode($raw, true);
    }

    /**
     * Push costume attributes to the request,
     * uses for costume request to server.
     *
     * @param array $push_attributes Push a attributes as array
     *
     * @return self
     */
    public function with(array $push_attributes)
    {
        $this->attributes = array_merge($this->attributes, $push_attributes);

        return $this;
    }

    /**
     * Get all request as array.
     *
     * @return array All request
     */
    public function all()
    {
        return array_merge(
            $this->headers,
            $this->query,
            $this->post,
            $this->attributes,
            $this->cookies,
            ['files' => $this->files],
            [
              'x-raw'     => $this->rawBodyCallback ? ($this->rawBodyCallback)() : null,
              'x-method'  => $this->method,
            ],
            $this->getJsonBody() ?? []
        );
    }

    /**
     * Get all request and wrap it.
     *
     * @return array Insert all request array in single array
     */
    public function wrap()
    {
        return [$this->all()];
    }
}
