<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class IfTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        return preg_replace(
            '/{%\s*if\s+([^%]+)\s*%}(.*?){%\s*endif\s*%}/s',
            '<?php if ($1): ?>$2<?php endif; ?>',
            preg_replace(
                '/{%\s*if\s+([^%]+)\s*%}(.*?){%\s*else\s*%}(.*?){%\s*endif\s*%}/s',
                '<?php if ($1): ?>$2<?php else: ?>$3<?php endif; ?>',
                $template
            )
        );
    }
}
