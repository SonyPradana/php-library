<?php

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
     *
     * @var array<string, string>
     */
    private $headers = [];

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
        $this->setHeaders($headers);
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
        $respone_header = sprintf('HTTP/1.1 %s %s', $respone_code, $respone_text);

        $headers = [];
        foreach ($this->headers as $header_name => $header) {
            $headers[] = $header_name . ': ' . $header;
        }

        $header_lines = implode("\r\n", $headers);
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
        $this->headers['Content-Type'] = $this->content_type;
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
    private function sendContent()
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
        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg'], true)) {
            static::closeOutputBuffers(0, true);
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
     * @param array<string, string> $headers
     *
     * @return self
     */
    public function setHeaders($headers)
    {
        // flush headers
        $this->headers = [];

        foreach ($headers as $header_name => $header) {
            if (is_numeric($header_name)) {
                if (!str_contains($header, ':')) {
                    continue;
                }

                [$header_name, $header] = explode(':', $header, 2);
            }
            $this->header(\trim($header_name), \trim($header));
        }

        return $this;
    }

    /**
     * Remove header from origin header.
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
     * @return self
     */
    public function header(string $header, ?string $value = null)
    {
        $header_name = $header;
        $header_val  = $value;

        if ($value === null && \str_contains($header, ':')) {
            [$header_name, $header_val] = \explode(':', $header, 2);
        }

        $this->headers[\trim($header_name)] = \trim($header_val ?? '');

        return $this;
    }

    /**
     * Get entry header.
     *
     * @return array<string, string>
     */
    public function getHeaders()
    {
        return $this->headers;
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
                $this->header($rule, $request->getHeaders($rule));
            }
        }

        return $this;
    }
}
