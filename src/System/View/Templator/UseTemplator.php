<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class UseTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        preg_match('/{%\s*use\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/', $template, $matches);

        $result = preg_replace_callback(
            '/{%\s*use\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/',
            function ($matches) {
                $this->uses[] = $matches[1];

                return '';
            },
            $template
        );

        if (0 === count($this->uses)) {
            return $template;
        }

        $uses      = array_map(fn ($use) => "use {$use};", $this->uses);
        $uses      = implode("\n", $uses);
        $header    = "<?php\n/* begain uses */\n{$uses}\n/* end uses */\n?>\n";

        return $header . $result;
    }
}
