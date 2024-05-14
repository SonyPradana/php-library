<?php

declare(strict_types=1);

namespace System\Http;

class Url
{
    private ?string $schema;
    private ?string $host;
    private ?int $port;
    private ?string $user;
    private ?string $password;
    private ?string $path;
    /**
     * @var array<int|string, string>|null
     */
    private ?array $query = null;
    private ?string $fragment;

    /**
     * @param array<string, string|int|array<int|string, string>|null> $parse_url
     */
    public function __construct(array $parse_url)
    {
        $this->schema    = $parse_url['scheme'] ?? null;
        $this->host      = $parse_url['host'] ?? null;
        $this->port      = $parse_url['port'] ?? null;
        $this->user      = $parse_url['user'] ?? null;
        $this->password  = $parse_url['pass'] ?? null;
        $this->path      = $parse_url['path'] ?? null;
        $this->fragment  = $parse_url['fragment'] ?? null;

        if (array_key_exists('query', $parse_url)) {
            $this->query = $this->parseQuery($parse_url['query']);
        }
    }

    /**
     * @return array<int|string, string>
     */
    private function parseQuery(string $query): array
    {
        $result = [];
        parse_str($query, $result);

        return $result;
    }

    public static function parse(string $url): self
    {
        return new self(parse_url($url));
    }

    public static function fromRequest(Request $from): self
    {
        return new self(parse_url($from->getUrl()));
    }

    /**
     * @return string|null
     */
    public function schema()
    {
        return $this->schema;
    }

    /**
     * @return string|null
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * @return int|null
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * @return string|null
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * @return string|null
     */
    public function path()
    {
        return $this->path;
    }

    /**
     * @return array<int|string, string>|null
     */
    public function query()
    {
        return $this->query;
    }

    /**
     * @return string|null
     */
    public function fragment()
    {
        return $this->fragment;
    }

    public function hasSchema(): bool
    {
        return null !== $this->schema;
    }

    public function hasHost(): bool
    {
        return null !== $this->host;
    }

    public function hasPort(): bool
    {
        return null !== $this->port;
    }

    public function hasUser(): bool
    {
        return null !== $this->user;
    }

    public function hasPassword(): bool
    {
        return null !== $this->password;
    }

    public function hasPath(): bool
    {
        return null !== $this->path;
    }

    public function hasQuery(): bool
    {
        return null !== $this->query;
    }

    public function hasFragment(): bool
    {
        return null !== $this->fragment;
    }
}
