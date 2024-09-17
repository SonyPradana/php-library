<?php

declare(strict_types=1);

namespace System\View\Templator;

use System\View\AbstractTemplatorParse;

class JsonTemplator extends AbstractTemplatorParse
{
    public function parse(string $template): string
    {
        return preg_replace_callback(
            '/{%\s*json\(\s*(.+?)\s*(?:,\s*(\d+)\s*)?(?:,\s*(\d+)\s*)?\)\s*%}/',
            static function ($matches): string {
                $data  = $matches[1];
                $flags = $matches[2] ?? 0;
                $depth = $matches[3] ?? 512;

                return "<?php echo json_encode({$data}, {$flags} | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR, {$depth}); ?>";
            },
            $template
        );
    }
}
