<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class EachTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        $template = preg_replace(
            '/{%\s*foreach\s+([^%]+)\s+as\s+([^%]+)\s*=>\s*([^%]+)\s*%}/s',
            '<?php foreach ($1 as $2 => $3): ?>',
            $template
        );

        $template = preg_replace(
            '/{%\s*foreach\s+([^%]+)\s+as\s+([^%]+)\s*%}/s',
            '<?php foreach ($1 as $2): ?>',
            $template
        );

        $template = preg_replace(
            '/{%\s*endforeach\s*%}/s',
            '<?php endforeach; ?>',
            $template
        );

        return $template;
    }
}
