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
        preg_match('/{%\s*extend\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/', $template, $matches_layout);
        if (!array_key_exists(1, $matches_layout)) {
            return $template;
        }
        $templatePath = $this->templateDir . '/' . $matches_layout[1];

        if (!file_exists($templatePath)) {
            throw new \Exception('Template file not found: ' . $matches_layout[1]);
        }

        $layout = file_get_contents($templatePath);

        $template = preg_replace_callback(
            '/{%\s*section\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}(.*?){%\s*endsection\s*%}/s',
            fn ($matches) => $this->sections[$matches[1]] = trim($matches[2]),
            $template
        );

        $template = preg_replace_callback(
            "/{%\s*yield\(\'(\w+)\'\)\s*%}/",
            fn ($matches) => array_key_exists($matches[1], $this->sections)
                ? $this->sections[$matches[1]]
                : throw new \Exception("Slot with extends '{$matches_layout[1]}' required '{$matches[1]}'"),
            $layout
        );

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
