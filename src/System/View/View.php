<?php

declare(strict_types=1);

namespace System\View;

use System\Http\Response;
use System\View\Exceptions\ViewFileNotFound;

class View
{
    /**
     * Render view template with data.
     *
     * @param string               $view_path View path location
     * @param array<string, mixed> $portal    Data to push
     *
     * @return Response
     *
     * @throw ViewFileNotFound
     */
    public static function render(string $view_path, array $portal = [])
    {
        if (!file_exists($view_path)) {
            throw new ViewFileNotFound($view_path);
        }

        $auth         = new Portal($portal['auth'] ?? []);
        $meta         = new Portal($portal['meta'] ?? []);
        $content      = new Portal($portal['contents'] ?? []);

        // get render content
        ob_start();
        require_once $view_path;
        $html = ob_get_clean();

        // send render content to client
        return (new Response())
            ->setContent($html)
            ->setResponeCode(Response::HTTP_OK)
            ->removeHeader([
                'Expires',
                'Pragma',
                'X-Powered-By',
                'Connection',
                'Server',
            ]);
    }
}
