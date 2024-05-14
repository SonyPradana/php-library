<?php

declare(strict_types=1);

namespace System\Http;

class Response
{
    public const HTTP_OK                            = 200;
    public const HTTP_CREATED                       = 201;
    public const HTTP_ACCEPTED                      = 202;
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const HTTP_NO_CONTENT                    = 204;
    public const HTTP_MOVED_PERMANENTLY             = 301;
    public const HTTP_BAD_REQUEST                   = 400;
    public const HTTP_UNAUTHORIZED                  = 401;
    public const HTTP_PAYMENT_REQUIRED              = 402;
    public const HTTP_FORBIDDEN                     = 403;
    public const HTTP_NOT_FOUND                     = 404;
    public const HTTP_METHOD_NOT_ALLOWED            = 405;

    /**
     * status respone text.
     *
     * @var array<int, string>
     */
    public static $statusTexts = [
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        301 => 'Moved Permanently',
        304 => 'Not Modified',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
    ];

    // property
    /**
     * Http body content.
     *
     * @var string|array<mixed, mixed>
     */
    private $content;

    /**
     * http respone code.
     *
     * @var int
     */
    private $respone_code;

    /**
     * Header array pools.
     */
    public HeaderCollection $headers;

    /**
     * List header to be hide/remove to client.
     *
     * @var array<int, string>
     */
    private $remove_headers = [];

    /**
     * Content type.
     *
     * @var string
     * */
    private $content_type = 'text/html';

    /**
     * Http Protocol version (1.0 or 1.1).
     */
    private string $protocol_version;

    /**
     * Create rosone http base on conten and header.
     *
     * @param string|array<mixed, mixed> $content      Content to serve to client
     * @param int                        $respone_code Respone code
     * @param array<string, string>      $headers      Header tosend to client
     */
    public function __construct($content = '', int $respone_code = Response::HTTP_OK, array $headers = [])
    {
        $this->setContent($content);
        $this->setResponeCode($respone_code);
        $this->headers = new HeaderCollection($headers);
        $this->setProtocolVersion('1.1');
    }

    /**
     * Get raw http respone include http version, header, content.
     *
     * @return string
     */
    public function __toString()
    {
        $respone_code   = $this->respone_code;
        $respone_text   = Response::$statusTexts[$respone_code] ?? 'ok';
        $respone_header = sprintf('HTTP/%s %s %s', $this->getProtocolVersion(), $respone_code, $respone_text);

        $header_lines = (string) $this->headers;
        $content      = is_array($this->content)
            ? json_encode($this->content, JSON_NUMERIC_CHECK)
            : $this->content;

        return
            $respone_header . "\r\n" .
            $header_lines . "\r\n" .
            "\r\n" .
            $content;
    }

    /**
     * Send header to client from header array pool,
     * include respone code.
     *
     * @return void
     */
    private function sendHeaders()
    {
        if (headers_sent()) {
            return;
        }
        // header respone code
        $respone_code     = $this->respone_code;
        $respone_text     = Response::$statusTexts[$respone_code] ?? 'unknown status';
        $respone_template = sprintf('HTTP/1.1 %s %s', $respone_code, $respone_text);
        header($respone_template);

        // header
        $this->headers->set('Content-Type', $this->content_type);
        // add costume header
        foreach ($this->headers as $key => $header) {
            header($key . ':' . $header);
        }

        // remove header
        if ($this->remove_headers === null) {
            header_remove();
        } else {
            foreach ($this->remove_headers as $header) {
                header_remove($header);
            }
        }
    }

    /**
     * Print/echo conten to client,
     * also send header to clinet.
     *
     * @return void
     */
    protected function sendContent()
    {
        echo is_array($this->content)
            ? json_encode($this->content, JSON_NUMERIC_CHECK)
            : $this->content;
    }

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     *
     * @final
     *
     * @return void
     */
    public static function closeOutputBuffers(int $targetLevel, bool $flush)
    {
        $status = ob_get_status(true);
        $level  = \count($status);
        $flags  = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * Send data to client.
     *
     * @return self
     */
    public function send()
    {
        $this->sendHeaders();
        $this->sendContent();

        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();

            return $this;
        }

        if (\function_exists('litespeed_finish_request')) {
            \litespeed_finish_request();

            return $this;
        }

        if (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffers(0, true);
            flush();

            return $this;
        }

        return $this;
    }

    /**
     * Send data to client with json format.
     *
     * @param string|array<mixed, mixed> $content Content to send data
     *
     * @return self
     */
    public function json($content = null)
    {
        $this->content_type = 'application/json';

        if ($content != null) {
            $this->setContent($content);
        }

        return $this;
    }

    /**
     * Send data to client with html format.
     *
     * @param bool $minify If true html tag will be send minify
     *
     * @return self
     */
    public function html(bool $minify = false)
    {
        $this->content_type = 'text/html';

        if (!is_array($this->content) && $minify) {
            /** @var string */
            $string_content = $this->content;
            $string_content =  $this->minify($string_content);

            $this->setContent($string_content);
        }

        return $this;
    }

    /**
     * Send data to client with plan format.
     *
     * @return self
     */
    public function planText()
    {
        $this->content_type = 'text/html';

        return $this;
    }

    /**
     * Minify html conntent.
     *
     * @param string $content Raw html content
     *
     * @return string
     */
    private function minify(string $content)
    {
        $search = [
            '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
            '/[^\S ]+\</s',     // strip whitespaces before tags, except space
            '/(\s)+/s',         // shorten multiple whitespace sequences
            '/<!--(.|\s)*?-->/', // Remove HTML comments
        ];

        $replace = [
            '>',
            '<',
            '\\1',
            '',
        ];

        return preg_replace($search, $replace, $content) ?? $content;
    }

    /**
     * Its instant of exit apilication.
     *
     * @return void
     */
    public function close()
    {
        exit;
    }

    /**
     * Set Content.
     *
     * @param string|array<mixed, mixed> $content Raw Content
     *
     * @return self
     */
    public function setContent($content)
    {
        $this->content  = $content;

        return $this;
    }

    /**
     * Set repone code (override).
     *
     * @return self
     */
    public function setResponeCode(int $respone_code)
    {
        $this->respone_code = $respone_code;

        return $this;
    }

    /**
     * Set header pools (overide).
     *
     * @deprecated use headers property instead
     *
     * @param array<string, string> $headers
     *
     * @return self
     */
    public function setHeaders($headers)
    {
        $this->headers->clear();

        foreach ($headers as $header_name => $header) {
            if (is_numeric($header_name)) {
                if (!str_contains($header, ':')) {
                    continue;
                }

                $this->headers->setRaw($header);
                continue;
            }

            $this->headers->set($header_name, $header);
        }

        return $this;
    }

    /**
     * Set http protocol version.
     */
    public function setProtocolVersion(string $version): self
    {
        $this->protocol_version = $version;

        return $this;
    }

    /**
     * Remove header from origin header.
     *
     * @deprecated use headers property instead
     *
     * @param array<int, string> $headers
     *
     * @return self
     */
    public function removeHeader($headers = [])
    {
        $this->remove_headers = [];
        foreach ($headers as $header) {
            $this->remove_headers[] = $header;
        }

        return $this;
    }

    /**
     * Add new header to headers pools.
     *
     * @deprecated use headers property instead
     *
     * @return self
     */
    public function header(string $header, ?string $value = null)
    {
        if (null === $value) {
            $this->headers->setRaw($header);

            return $this;
        }

        $this->headers->set($header, $value);

        return $this;
    }

    /**
     * Get entry header.
     *
     * @deprecated use headers property instead
     *
     * @return array<string, string>
     */
    public function getHeaders()
    {
        return $this->headers->toArray();
    }

    public function getStatusCode(): int
    {
        return $this->respone_code;
    }

    /**
     * @return string|array<mixed, mixed>
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get http protocole version.
     */
    public function getProtocolVersion(): string
    {
        return $this->protocol_version;
    }

    /**
     * Prepare response to send header to client.
     *
     * The respone header will follow respone request
     *
     * @param Request            $request     Http Web Request
     * @param array<int, string> $header_name Respone header will be follow from request
     *
     * @return self
     */
    public function followRequest(Request $request, array $header_name = [])
    {
        $follow_rule = array_merge($header_name, [
            'cache-control',
            'conten-type',
        ]);

        // header based on the Request
        foreach ($follow_rule as $rule) {
            if ($request->hasHeader($rule)) {
                $this->headers->set($rule, $request->getHeaders($rule));
            }
        }

        return $this;
    }

    /**
     * Informational status code 1xx.
     */
    public function isInformational(): bool
    {
        return $this->respone_code > 99 && $this->respone_code < 201;
    }

    /**
     * Successful status code 2xx.
     */
    public function isSuccessful(): bool
    {
        return $this->respone_code > 199 && $this->respone_code < 301;
    }

    /**
     * Redirection status code 3xx.
     */
    public function isRedirection(): bool
    {
        return $this->respone_code > 299 && $this->respone_code < 401;
    }

    /**
     * Client error status code 4xx.
     */
    public function isClientError(): bool
    {
        return $this->respone_code > 399 && $this->respone_code < 501;
    }

    /**
     * Server error status code 5xx.
     */
    public function isServerError(): bool
    {
        return $this->respone_code > 499 && $this->respone_code < 601;
    }
}
