<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class NamespaceTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        preg_match('/{%\s*use\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/', $template, $matches);

        $result = preg_replace_callback(
            '/{%\s*use\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/',
            function ($matches) {
                $this->namespaces[] = $matches[1];

                return '';
            },
            $template
        );

        if (null === $this->namespaces) {
            return '';
        }

        $namespace = array_map(fn ($use) => "namespace {$use};", $this->namespaces);
        $namespace = implode("\n", $namespace);
        $header    = "<?php\n/* begain namespace */\n{$namespace}\n/* end namespace */\n?>\n";

        return $header . $result;
    }
}
