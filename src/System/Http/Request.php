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
     *
     * @var Collection<string, string>
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
     *
     * @var Collection<string, string>
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
     *
     * @var Collection<string, string>
     */
    private Collection $json;

    /**
     * Initialize mime format.
     *
     * @var array<string, string[]>
     *
     * @see https://github.com/symfony/symfony/blob/5.4/src/Symfony/Component/HttpFoundation/Request.php
     */
    protected array $formats = [
        'html'   => ['text/html', 'application/xhtml+xml'],
        'txt'    => ['text/plain'],
        'js'     => ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css'    => ['text/css'],
        'json'   => ['application/json', 'application/x-json'],
        'jsonld' => ['application/ld+json'],
        'xml'    => ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf'    => ['application/rdf+xml'],
        'atom'   => ['application/atom+xml'],
        'rss'    => ['application/rss+xml'],
        'form'   => ['application/x-www-form-urlencoded', 'multipart/form-data'],
    ];

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

    /**
     * Initial request.
     *
     * @param array<string, string>|null $query
     * @param array<string, string>|null $post
     * @param array<string, string>|null $attributes
     * @param array<string, string>|null $cookies
     * @param array<string, string>|null $files
     * @param array<string, string>|null $headers
     *
     * @return static
     */
    public function duplicate(
        ?array $query = null,
        ?array $post = null,
        ?array $attributes = null,
        ?array $cookies = null,
        ?array $files = null,
        ?array $headers = null
    ) {
        $dupplicate = clone $this;

        if (null !== $query) {
            $dupplicate->query = new Collection($query);
        }
        if (null !== $post) {
            $dupplicate->post = new Collection($post);
        }
        if (null !== $attributes) {
            $dupplicate->attributes = $attributes;
        }
        if (null !== $cookies) {
            $dupplicate->cookies = $cookies;
        }
        if (null !== $files) {
            $dupplicate->files = $files;
        }
        if (null !== $headers) {
            $dupplicate->headers = $headers;
        }

        return $dupplicate;
    }

    public function __clone()
    {
        $this->query      = clone $this->query;
        $this->post       = clone $this->post;
        // cloning as array
        $this->attributes = (new Collection($this->attributes))->all();
        $this->cookies    = (new Collection($this->cookies))->all();
        $this->files      = (new Collection($this->files))->all();
        $this->headers    = (new Collection($this->headers))->all();
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get query ($_GET).
     *
     * @return CollectionImmutable<string, string>
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
    public function getQuery(?string $key = null)
    {
        if (func_num_args() === 0) {
            return $this->query->all();
        }

        return $this->query->get($key);
    }

    /**
     * Get post ($_POST).
     *
     * @return CollectionImmutable<string, string>
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
    public function getPost(?string $key = null)
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
    public function getFile(?string $key = null)
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
    public function getHeaders(?string $header = null)
    {
        if ($header === null) {
            return $this->headers;
        }

        return $this->headers[$header] ?? null;
    }

    /**
     * Gets the mime types associated with the format.
     *
     * @return string[]
     */
    public function getMimeTypes(string $format): array
    {
        return $this->formats[$format] ?? [];
    }

    /**
     * Gets format using mimetype.
     *
     * @param string|null $mime_type
     *
     * @return string|null
     */
    public function getFormat($mime_type)
    {
        foreach ($this->formats as $format => $mime_types) {
            if (in_array($mime_type, $mime_types)) {
                return $format;
            }
        }

        return null;
    }

    /**
     * Gets format type from request header.
     *
     * @return string|null
     */
    public function getRequestFormat()
    {
        $content_type = $this->getHeaders('content-type');

        return $this->getFormat($content_type);
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
        /** @var Collection<string, string> */
        $input = $this->input();

        $all = array_merge(
            $this->headers,
            $input->toArray(),
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

    /**
     * @return Collection<string, string>
     */
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
     * @template TGetDefault
     *
     * @param TGetDefault $default
     *
     * @return Collection<string, string>|string|TGetDefault
     */
    public function input(?string $key = null, $default = null)
    {
        $input = $this->source()->add($this->query->all());
        if (null === $key) {
            return $input;
        }

        return $input->get($key, $default);
    }

    /**
     * Get input resource base on method type.
     *
     * @return Collection<string, string>
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

    /**
     * Get the value at the given offset.
     *
     * @param string $offset
     *
     * @return string|null
     */
    #[\ReturnTypeWillChange]
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
