<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class PHPTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        return preg_replace(
            '/{%\s*php\s*%}(.*?){%\s*endphp\s*%}/s',
            '<?php $1 ?>',
            $template
        );
    }
}
