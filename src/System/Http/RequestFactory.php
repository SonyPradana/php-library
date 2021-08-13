<?php

namespace System\Http;

class RequestFactory
{
  public function getFromGloball(): Request
  {
    return new Request(
      $_SERVER['REQUEST_URI'] ?? '',
      $_GET,
      $_POST,
      [],
      $_COOKIE,
      $_FILES,
      $this->getHeaders(),
      $this->getMethod(),
      $this->getClient(),
      fn(): string => file_get_contents('php://input')
    );
  }

  private function getHeaders(): array
  {
    if (!function_exists('apache_request_headers')) {
			return array_change_key_case(
        apache_request_headers()
      );
		}

		$headers = array();
    foreach ($_SERVER as $key => $value) {
      if (strncmp($key, 'HTTP_', 5) === 0) {
        $key = substr($key, 5);
      } elseif (strncmp($key, 'CONTENT_', 8)) {
        continue;
      }
      $headers[strtr($key, '_', '-')] = $value;
    }

    if (! isset($headers['Authorization'])) {
      if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
      } elseif (isset($_SERVER['PHP_AUTH_USER'])) {
        $basic_pass = $_SERVER['PHP_AUTH_PW'] ?? '';
        $headers['Authorization'] = 'Basic ' . base64_encode($_SERVER['PHP_AUTH_USER'] . ':' . $basic_pass);
      } elseif (isset($_SERVER['PHP_AUTH_DIGEST'])) {
        $headers['Authorization'] = $_SERVER['PHP_AUTH_DIGEST'];
      }
    }

    return array_change_key_case($headers);
  }

  private function getMethod(): ?string
	{
		$method = $_SERVER['REQUEST_METHOD'] ?? null;
		if (
			$method === 'POST'
			&& preg_match('#^[A-Z]+$#D', $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'] ?? '')
		) {
			$method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
		}
		return $method;
	}

  private function getClient(): ?string
	{
		return !empty($_SERVER['REMOTE_ADDR'])
			? trim($_SERVER['REMOTE_ADDR'], '[]')
			: null;
	}
}
