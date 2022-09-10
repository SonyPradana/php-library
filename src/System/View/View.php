<?php

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
        $content_type = $portal['header']['content_type'] ?? 'Content-Type: text/html';

        // get render content
        ob_start();
        require_once $view_path;
        $html = ob_get_clean();

        // send render content to client
        return (new Response())
            ->setContent($html)
            ->setResponeCode(\System\Http\Response::HTTP_OK)
            ->header($content_type)
            ->removeHeader([
                'Expires',
                'Pragma',
                'X-Powered-By',
                'Connection',
                'Server',
            ]);
    }
}
