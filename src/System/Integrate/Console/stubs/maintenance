<?php

declare(strict_types=1);

if (false === file_exists($down = __DIR__ . DIRECTORY_SEPARATOR . 'down')) {
    return;
}

/** @var array<string, mixed> */
$data = require_once $down;

if (! isset($data['template'])) {
    return;
}

if (isset($data['redirect']) && $_SERVER['REQUEST_URI'] !== $data['redirect']) {
    http_response_code(302);
    header('Location: '.$data['redirect']);

    exit;
}

http_response_code($data['status'] ?? 503);

if (isset($data['retry'])) {
    header('Retry-After: '.$data['retry']);
}

echo $data['template'];

exit;