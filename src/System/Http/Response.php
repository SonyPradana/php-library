<?php

namespace System\Http;

class Response
{
  public const HTTP_OK = 200;
  public const HTTP_CREATED = 201;
  public const HTTP_ACCEPTED = 202;
  public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
  public const HTTP_NO_CONTENT = 204;
  public const HTTP_MOVED_PERMANENTLY = 301;
  public const HTTP_BAD_REQUEST = 400;
  public const HTTP_UNAUTHORIZED = 401;
  public const HTTP_PAYMENT_REQUIRED = 402;
  public const HTTP_FORBIDDEN = 403;
  public const HTTP_NOT_FOUND = 404;
  public const HTTP_METHOD_NOT_ALLOWED = 405;

  // status respone text
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
  private $content;
  private $respone_code;

  /** Header array pools */
  private $headers;
  private $is_array;  // content type
  private $remove_headers = [];

  /** @var string Default content type */
  private $content_type = 'Content-Type: text/html';

  /**
   * Create rosone http base on conten and header
   *
   * @param string|array $content Content to serve to client
   * @param int $respone_code Respone code
   * @param array $headers Header tosend to client
   */
  public function __construct($content = '', int $respone_code = Response::HTTP_OK, array $headers = [])
  {
    $headers_content = $content['headers'] ?? [];
    // remove header information
    if (isset($content['headers'])) {
      unset($content['headers']);
    }

    $this->content = $content;
    $this->respone_code = $respone_code;
    $this->headers = array_merge($headers, $headers_content);

    $this->is_array = is_array($content);
  }

  /**
   * Send header to client from header array pool,
   * include respone code
   */
  private function sendHeaders(): void
  {
    if (headers_sent()) {
      return;
    }
    // header respone code
    $respone_code = $this->respone_code;
    $respone_text = Response::$statusTexts[$respone_code] ?? 'unknown status';
    $respone_template = sprintf("HTTP/1.1 %s %s", $respone_code, $respone_text);
    header($respone_template);

    // header
    $this->headers[] = $this->content_type;
    // add costume header
    foreach ($this->headers as $header) {
      header($header);
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
   * also send header to clinet
   */
  private function sendContent(): void
  {
    // print data to client
    if ($this->is_array) {
      echo json_encode($this->content, JSON_NUMERIC_CHECK);
    } else {
      echo $this->content;;
    }
  }

  /**
   * Cleans or flushes output buffers up to target level.
   *
   * Resulting level can be greater than target level if a non-removable buffer has been encountered.
   *
   * @final
   */
  public static function closeOutputBuffers(int $targetLevel, bool $flush): void
  {
    $status = ob_get_status(true);
    $level = \count($status);
    $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

    while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
      if ($flush) {
        ob_end_flush();
      } else {
        ob_end_clean();
      }
    }
  }

  /**
   * Send data to client
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
   * Send data to client with json format
   *
   * @param string|array $content
   *  Content to send data
   */
  public function json($content = null)
  {
    $this->content_type = 'Content-Type: application/json';

    if ($content != null) {
      $this->setContent($content);
    }

    return $this;
  }

   /**
   * Send data to client with html format
   *
   * @param bool $minify
   *  If true html tag will be send minify
   */
  public function html(bool $minify = false)
  {
    $this->content_type = 'Content-Type: text/html';

    if (! $this->is_array && $minify) {
      $this->setContent($this->minify($this->content));
    }

    return $this;
  }

  /**
   * Send data to client with plan format
   */
  public function planText()
  {
    $this->content_type = 'Content-Type: text/html';

    return $this;
  }

  /**
   * Minify html conntent
   *
   * @param string $content Raw html content
   */
  private function minify(string $content)
  {
    $search = array(
      '/\>[^\S ]+/s',     // strip whitespaces after tags, except space
      '/[^\S ]+\</s',     // strip whitespaces before tags, except space
      '/(\s)+/s',         // shorten multiple whitespace sequences
      '/<!--(.|\s)*?-->/' // Remove HTML comments
    );

    $replace = array(
      '>',
      '<',
      '\\1',
      ''
    );

    return preg_replace($search, $replace, $content);
  }

  /**
   * Its instant of exit apilication
   */
  public function close()
  {
    exit;
  }

  /**
   * Set Content
   *
   * @param string|array $content Raw Content
   */
  public function setContent($content)
  {
    // remove header information
    if (isset($content['headers'])) {
      unset($content['headers']);
    }

    $this->content = $content;
    $this->is_array = is_array($content);

    return $this;
  }

  /**
   * Set repone code (override)
   *
   * @return $this
   */
  public function setResponeCode(int $respone_code): object
  {
    $this->respone_code = $respone_code;

    return $this;
  }

  /**
   * Set header pools (overide)
   */
  public function setHeaders(array $headers)
  {
    $this->headers = $headers;

    return $this;
  }

  /**
   * Remove header from origin header
   *
   * @param array|null $headers
   *
   */
  public function removeHeader(?array $headers = null)
  {
    $this->remove_headers = $headers;

    return $this;
  }

  /**
   * Add new header to headers pools
   */
  public function header(string $header, ?string $value = null)
  {
    $this->headers[] = $value === null
      ? $header
      : $header . ': ' . $value;

    return $this;
  }

  /**
   * Prepare response to send header to client.
   *
   * The respone header will follow respone request
   *
   * @param Request $request Http Web Request
   * @param array $headers Respone header will be follow from request
   *
   * @return $this
   *
   */
  public function followRequest(Request $request, array $headers = [])
  {
    $follow_rule = array_merge($headers, [
      'cache-control',
      'conten-type'
    ]);
    // header based on the Request
    foreach ($follow_rule as $rule) {
      if ($request->hasHeader($rule)) {
        $this->headers[] = $request->getHeaders($rule);
      }
    }

    return $this;
  }
}
