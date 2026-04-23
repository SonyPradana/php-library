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

        // Protect existing PHP blocks
        $phpBlocks = [];
        $template  = preg_replace_callback('/<\?php.*?\?>/s', function ($matches) use (&$phpBlocks) {
            $phpBlocks[] = $matches[0];

            return '##PHP_BLOCK_PLACEHOLDER##';
        }, $template);

        $template = preg_replace('/{!!\s*([^}]+)\s*!!}/', '<?php echo $1; ?>', $template);
        $template = preg_replace('/{{\s*([^}]+)\s*}}/', '<?php echo htmlspecialchars($1); ?>', $template);

        // Restore PHP blocks
        foreach ($phpBlocks as $phpBlock) {
            $template = preg_replace('/##PHP_BLOCK_PLACEHOLDER##/', $phpBlock, $template, 1);
        }

        foreach ($rawBlocks as $rawBlock) {
            $template = preg_replace('/##RAW_BLOCK##/', $rawBlock, $template, 1);
        }

        return $template;
    }
}
