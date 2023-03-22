<?php

declare(strict_types=1);

namespace System\View;

use System\View\Exceptions\ViewFileNotFound;

class Templator
{
    private $templateDir;
    private $cacheDir;
    private $sections = [];

    public function __construct(string $templateDir, string $cacheDir)
    {
        $this->templateDir = $templateDir;
        $this->cacheDir    = $cacheDir;
    }

    public function render(string $templateName, array $data, bool $cache = true): string
    {
        $output       = '';
        $templatePath = $this->templateDir . '/' . $templateName;

        if (!file_exists($templatePath)) {
            throw new ViewFileNotFound($templatePath);
        }

        $cachePath = $this->cacheDir . '/' . md5($templateName) . '.php';

        if ($cache && file_exists($cachePath) && filemtime($cachePath) >= filemtime($templatePath)) {
            extract($data);
            include $cachePath;

            return trim($output);
        }

        $template = file_get_contents($templatePath);
        $template = $this->templates($template);

        // Generate PHP code
        $phpCode = '<?php ob_start(); ?>' . $template . '<?php $output = ob_get_clean(); ?>';

        file_put_contents($cachePath, $phpCode);

        extract($data);
        include $cachePath;

        return trim($output);
    }

    private function templates(string $template): string
    {
        $template = $this->templateSlot($template);
        $template = $this->templateInclude($template);
        $template = $this->templatePhp($template);
        $template = $this->templateName($template);
        $template = $this->templateIf($template);
        $template = $this->templateEach($template);

        return $template;
    }

    private function templateSlot($template): string
    {
        $template = preg_replace_callback(
            '/{%\s*section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\,\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}(.*?){%\s*endsection\s*%}/s',
            function ($matches) {
                $this->sections[$matches[2]] = [
                    'name' => trim($matches[3]),
                    'loc'  => $matches[1],
                ];

                return trim($matches[3]);
            }, $template);

        $layouts = [];
        foreach ($this->sections as $name => $content) {
            if (!array_key_exists($content['loc'], $layouts)) {
                $templatePath             = $this->templateDir . '/' . $content['loc'];
                $layouts[$content['loc']] = file_get_contents($templatePath);
            }

            $template = $layouts[$content['loc']] = preg_replace_callback(
                "/{%\s*yield\(\'(\w+)\'\)\s*%}/",
                fn ($matches) => str_replace($matches[0], $content['name'], $layouts[$content['loc']]),
                $layouts[$content['loc']]
            );
        }

        return $template;
    }

    private function templateInclude($template): string
    {
        return preg_replace_callback('/{%\s*include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/', function ($matches) {
            $templatePath = $this->templateDir . '/' . $matches[1];

            if (!file_exists($templatePath)) {
                throw new \Exception('Template file not found: ' . $matches[1]);
            }

            $includedTemplate = file_get_contents($templatePath);

            return trim($includedTemplate, "\n");
        }, $template);
    }

    private function templateName(string $template): string
    {
        return preg_replace('/{{\s*([^}]+)\s*\?\?\s*([^:}]+)\s*:\s*([^}]+)\s*}}/',
            '<?php echo ($1 !== null) ? $1 : $3; ?>',
            preg_replace('/{{\s*([^}]+)\s*}}/', '<?php echo htmlspecialchars($$1); ?>', $template)
        );
    }

    private function templatePhp(string $template): string
    {
        return preg_replace('/{%\s*php\s*%}(.*?){%\s*endphp\s*%}/s', '<?php $1 ?>', $template);
    }

    private function templateIf(string $template): string
    {
        return preg_replace('/{%\s*if\s+([^%]+)\s*%}(.*?){%\s*endif\s*%}/s', '<?php if ($1): ?>$2<?php endif; ?>', $template);
    }

    private function templateEach(string $template): string
    {
        return preg_replace('/{%\s*foreach\s+([^%]+)\s+as\s+([^%]+)\s*%}(.*?){%\s*endforeach\s*%}/s', '<?php foreach ($$1 as $$2): ?>$3<?php endforeach; ?>', $template);
    }
}
