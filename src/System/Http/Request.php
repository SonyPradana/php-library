<?php

declare(strict_types=1);

namespace System\Http;

use System\Collection\Collection;
use System\Collection\CollectionImmutable;
use System\Support\Marco;
use System\Text\Str;

/**
 * @method \Validator\Validator    validate(?\Closure $rule = null, ?\Closure $filter = null)
 * @method \System\File\UploadFile upload(array $file_name)
 *
 * @implements \ArrayAccess<string, string>
 * @implements \IteratorAggregate<string, string>
 */
class Request implements \ArrayAccess, \IteratorAggregate
{
    use Marco;

    /**
     * Request method.
     */
    private string $method;

    /**
     * Request url.
     */
    private string $url;

    /**
     * Request query ($_GET).
     */
    private Collection $query;

    /**
     * Costume request information.
     *
     * @var array<string, string|int|bool>
     */
    private array $attributes;

    /**
     * Request post ($_POST).
     */
    private Collection $post;

    /**
     * Request file ($_FILE).
     *
     * @var array<string, array<int, string>|string>
     */
    private array $files;

    /**
     * Request cookies ($_COOKIES).
     *
     * @var array<string, string>
     */
    private array $cookies;

    /**
     * Request header.
     *
     * @var array<string, string>
     */
    private array $headers;

    /**
     * Request remote addres (IP).
     */
    private string $remoteAddress;

    /**
     * Request Body content.
     *
     * @var ?string
     */
    private $rawBody;

    /**
     * Json body rendered.
     */
    private Collection $json;

    /**
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
     * @param ?string               $rawBody
     */
    public function __construct(
        string $url,
        array $query = [],
        array $post = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $headers = [],
        string $method = 'GET',
        string $remoteAddress = '::1',
        ?string $rawBody = null
    ) {
        $this->initialize($url, $query, $post, $attributes, $cookies, $files, $headers, $method, $remoteAddress, $rawBody);
    }

    /**
     * Initial request.
     *
     * @param array<string, string> $query
     * @param array<string, string> $post
     * @param array<string, string> $attributes
     * @param array<string, string> $cookies
     * @param array<string, string> $files
     * @param array<string, string> $headers
     * @param ?string               $rawBody
     *
     * @return self
     */
    public function initialize(
        string $url,
        array $query = [],
        array $post = [],
        array $attributes = [],
        array $cookies = [],
        array $files = [],
        array $headers = [],
        string $method = 'GET',
        string $remoteAddress = '::1',
        ?string $rawBody = null
    ) {
        $this->url             = $url;
        $this->query           = new Collection($query);
        $this->post            = new Collection($post);
        $this->attributes      = $attributes;
        $this->cookies         = $cookies;
        $this->files           = $files;
        $this->headers         = $headers;
        $this->method          = $method;
        $this->remoteAddress   = $remoteAddress;
        $this->rawBody         = $rawBody;

        return $this;
    }

    public function getUrl(): string
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

    /**
     * Get Post/s ($_GET).
     *
     * @return array<string, string>|string
     */
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

    /**
     * Get Post/s ($_POST).
     *
     * @return array<string, string>|string
     */
    public function getPost(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->post->all();
        }

        return $this->post->get($key);
    }

    /**
     * Get file/s ($_FILE).
     *
     * @return array<string, array<int, string>|string>|array<int, string>|string
     */
    public function getFile(string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->files;
        }

        return $this->files[$key];
    }

    public function getCookie(string $key): string
    {
        return $this->cookies[$key] ?? null;
    }

    /**
     * Get cookies.
     *
     * @return array<string, string>|null
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    public function getMethod(): string
    {
        return \strtoupper($this->method);
    }

    public function isMethod(string $method): bool
    {
        return strcasecmp($this->method, $method) === 0;
    }

    /**
     * Get header/s.
     *
     * @return array<string, string>|string|null get header/s
     */
    public function getHeaders(string $header = null)
    {
        if ($header === null) {
            return $this->headers;
        }

        return $this->headers[$header] ?? null;
    }

    public function isHeader(string $header_key, string $header_val): bool
    {
        if (isset($this->headers[$header_key])) {
            return $this->headers[$header_key] === $header_val;
        }

        return false;
    }

    public function hasHeader(string $header_key): bool
    {
        return isset($this->headers[$header_key]);
    }

    public function isSecured(): bool
    {
        return !empty($_SERVER['HTTPS']) && strcasecmp($_SERVER['HTTPS'], 'off')
            ? true    // https
            : false;  // http;
    }

    public function getRemoteAddress(): string
    {
        return $this->remoteAddress;
    }

    public function getRawBody(): ?string
    {
        return $this->rawBody;
    }

    /**
     * Get Json array.
     *
     * @return array<mixed, mixed>
     *
     * @see https://github.com/symfony/symfony/blob/6.2/src/Symfony/Component/HttpFoundation/Request.php
     */
    public function getJsonBody()
    {
        if ('' === $content = $this->rawBody) {
            throw new \Exception('Request body is empty.');
        }

        try {
            $content = json_decode($content, true, 512, \JSON_BIGINT_AS_STRING | \JSON_THROW_ON_ERROR);
        } catch (\Exception $e) {
            throw new \Exception('Could not decode request body.', $e->getCode(), $e);
        }

        if (!\is_array($content)) {
            throw new \Exception(sprintf('JSON content was expected to decode to an array, "%s" returned.', get_debug_type($content)));
        }

        return $content;
    }

    /**
     * Get attribute.
     *
     * @param string|int|bool $default
     *
     * @return string|int|bool
     */
    public function getAttribute(string $key, $default)
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Push costume attributes to the request,
     * uses for costume request to server.
     *
     * @param array<string, string|int|bool> $push_attributes Push a attributes as array
     *
     * @return self
     */
    public function with($push_attributes)
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
        $all = array_merge(
            $this->headers,
            $this->input()->all(),
            $this->attributes,
            $this->cookies,
            [
                'x-raw'     => $this->getRawBody() ?? '',
                'x-method'  => $this->getMethod(),
                'files'     => $this->files,
            ]
        );

        return $all;
    }

    /**
     * Get all request and wrap it.
     *
     * @return array<int, array<string, mixed>> Insert all request array in single array
     */
    public function wrap()
    {
        return [$this->all()];
    }

    /**
     * Determinate request is ajax.
     */
    public function isAjax(): bool
    {
        return $this->getHeaders('X-Requested-With') == 'XMLHttpRequest';
    }

    /**
     * Determinate request is json request.
     */
    public function isJson(): bool
    {
        /** @var string */
        $content_type = $this->getHeaders('content-type') ?? '';

        return Str::contains($content_type, '/json') || Str::contains($content_type, '+json');
    }

    public function json(): Collection
    {
        if (!isset($this->json)) {
            $this->json = new Collection($this->getJsonBody());
        }

        return $this->json;
    }

    /**
     * Compine all request input.
     *
     * @param mixed $default
     */
    public function input(string $key = null, $default = null): Collection
    {
        $input = $this->source()->add($this->query->all());
        if (null === $key) {
            return $input;
        }

        return $input->get($key, $default);
    }

    /**
     * Get input resource base on method type.
     */
    private function source(): Collection
    {
        if ($this->isJson()) {
            return $this->json();
        }

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

    #[\ReturnTypeWillChange]
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
     * @param string $value
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

    /**
     * Iterator.
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->source()->all());
    }
}
