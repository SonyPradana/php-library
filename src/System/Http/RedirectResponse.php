<?php

declare(strict_types=1);

namespace System\Http;

class RedirectResponse extends Response
{
    public function __construct(string $url, int $response_code = 302, array $headers = [])
    {
        parent::__construct('', $response_code, $headers);
        $this->setTarget($url);
    }

    public function setTarget(string $url): void
    {
        $this->setContent(sprintf('<html><head><meta charset="UTF-8" /><meta http-equiv="refresh" content="0;url=\'%1$s\'" /><title>Redirecting to %1$s</title></head><body>Redirecting to <a href="%1$s">%1$s</a>.</body></html>', htmlspecialchars($url, \ENT_QUOTES, 'UTF-8')));
        $this->setHeaders([
            'Location' => $url,
        ]);
    }
}
