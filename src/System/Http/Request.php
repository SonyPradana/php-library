<?php

namespace System\Http;

use ArrayAccess;
use System\Collection\Collection;
use System\Collection\CollectionImmutable;

class Request implements ArrayAccess
{
    private $method;
    private $url;
    private Collection $query;
    private $attributes = [];
    private Collection $post;
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
        $this->query           = new Collection($query ?? []);
        $this->post            = new Collection($post ?? []);
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

    /**
     * Get query ($_GET).
     */
    public function query(): CollectionImmutable
    {
        return $this->query->immutable();
    }

    public function getQuery(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->query->all();
        }

        return $this->query->get($key);
    }

    /**
     * Get post ($_POST).
     */
    public function post(): CollectionImmutable
    {
        return $this->post->immutable();
    }

    public function getPost(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->post->all();
        }

        return $this->post->get($key);
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
     * @return array<string, mixed> All request
     */
    public function all()
    {
        return array_merge(
            $this->headers,
            $this->query->all(),
            $this->post->all(),
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

    /**
     * Get input resource base on method type.
     */
    private function source(): Collection
    {
        return in_array($this->method, ['GET', 'HEAD']) ? $this->query : $this->post;
    }

    /**
     * Determine if the given offset exists.
     *
     * @param string $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->source()->has($offset);
    }

    /**
     * Get the value at the given offset.
     *
     * @param string $offset
     *
     * @return string|null
     */
    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    /**
     * Set the value at the given offset.
     *
     * @param string $offset
     * @param mixed  $value
     */
    public function offsetSet($offset, $value): void
    {
        $this->source()->set($offset, $value);
    }

    /**
     * Remove the value at the given offset.
     *
     * @param string $offset
     */
    public function offsetUnset($offset): void
    {
        $this->source()->remove($offset);
    }

    /**
     * Get an input element from the request.
     *
     * @param string $key
     *
     * @return string|null
     */
    public function __get($key)
    {
        return $this->source()->get($key);
    }
}
