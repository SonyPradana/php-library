<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class NameTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        $rawBlocks = [];
        $template  = preg_replace_callback('/{% raw %}(.*?){% endraw %}/s', function ($matches) use (&$rawBlocks) {
            $rawBlocks[] = $matches[1];

            return '##RAW_BLOCK##';
        }, $template);

        $template = preg_replace('/{{\s*([^}]+)\s*\?\?\s*([^:}]+)\s*:\s*([^}]+)\s*}}/',
            '<?php echo ($1 !== null) ? $1 : $3; ?>',
            preg_replace('/{{\s*([^}]+)\s*}}/', '<?php echo htmlspecialchars($1); ?>', $template)
        );

        foreach ($rawBlocks as $rawBlock) {
            $template = preg_replace('/##RAW_BLOCK##/', $rawBlock, $template, 1);
        }

        return $template;
    }
}
