<?php

declare(strict_types=1);

namespace System\Integrate\Http\Middleware;

use System\Http\Request;
use System\Http\Response;
use System\RateLimiter\Interfaces\RateLimiterInterface;

class ThrottleMiddleware
{
    public function __construct(protected RateLimiterInterface $limiter)
    {
    }

    public function handle(Request $request, \Closure $next): Response
    {
        $key = $this->resolveRequestKey($request);
        if ($this->limiter->isBlocked($key, $maxAttempts = 60, $decayMinutes = 1)) {
            return $this->rateLimitedRespose(
                key: $key,
                maxAttempts: $maxAttempts,
                remaingAfter: $this->calculateRemainingAttempts(
                    key: $key,
                    maxAttempts: $maxAttempts,
                    retryAfter: $this->limiter->getRetryAfter($key)
                )
            );
        }

        $this->limiter->consume($key, $decayMinutes);

        /** @var Response */
        $respone = $next($request);

        $respone->headers->add(
            $this->rateLimitedHeader(
                maxAttempts: $maxAttempts,
                remaingAfter: $this->calculateRemainingAttempts(
                    key: $key,
                    maxAttempts: $maxAttempts,
                    retryAfter: null
                )
            )
        );

        return $respone;
    }

    protected function resolveRequestKey(Request $request): string
    {
        $key = $request->getRemoteAddress();

        return sha1(, $key);
    }

    protected function rateLimitedRespose(string $key, int $maxAttempts, int $remaingAfter, ?int $retryAfter = null): Response
    {
        return new Response('Too Many Requests', 429, $this->rateLimitedHeader($maxAttempts, $remaingAfter, $remaingAfter));
    }

    /**
     * @return array<string, string>
     */
    protected function rateLimitedHeader(int $maxAttempts, int $remaingAfter, ?int $retryAfter = null): array
    {
        $header = [
            'X-RateLimit-Limit'     => (string) $maxAttempts,
            'X-RateLimit-Remaining' => (string) $remaingAfter,
        ];

        if ($retryAfter !== null) {
            $header['Retry-After'] = (string) $retryAfter;
        }

        return $header;
    }

    public function calculateRemainingAttempts(string $key, int $maxAttempts, ?int $retryAfter): int
    {
        if (null !== $retryAfter) {
            return 0;
        }

        return $this->limiter->remaining($key, $maxAttempts);
    }
}
