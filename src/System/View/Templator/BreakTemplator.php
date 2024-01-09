<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class BreakTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        return preg_replace(
            '/\{%\s*break\s*(\d*)\s*%\}/',
            '<?php break $1; ?>',
            $template
        );
    }
}
