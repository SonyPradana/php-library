<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class IfTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        $tokens = [
            'if_open' => '/{%\s*if\s+([^%]+)\s*%}/',
            'else'    => '/{%\s*else\s*%}/',
            'endif'   => '/{%\s*endif\s*%}/',
        ];

        $replacements = [
            'if_open' => '<?php if ($1): ?>',
            'else'    => '<?php else: ?>',
            'endif'   => '<?php endif; ?>',
        ];

        $positions = [];

        foreach ($tokens as $type => $pattern) {
            preg_match_all($pattern, $template, $matches, PREG_OFFSET_CAPTURE);
            foreach ($matches[0] as $match) {
                $pos                = $match[0];
                $offset             = $match[1];
                $positions[$offset] = [
                    'type'   => $type,
                    'match'  => $pos,
                    'length' => strlen($pos),
                ];

                if ($type === 'if_open') {
                    preg_match($tokens['if_open'], $pos, $condition);
                    $positions[$offset]['condition'] = $condition[1];
                }
            }
        }

        ksort($positions);

        $result  = $template;
        $offsets = array_reverse(array_keys($positions));

        foreach ($offsets as $offset) {
            $item        = $positions[$offset];
            $type        = $item['type'];
            $replacement = $replacements[$type];

            if ($type === 'if_open') {
                $replacement = str_replace('$1', $item['condition'], $replacement);
            }

            $result = substr_replace(
                $result,
                $replacement,
                $offset,
                $item['length']
            );
        }

        return $result;
    }
}
