<?php

declare(strict_types=1);

namespace System\View;

use System\View\Exceptions\ViewFileNotFound;

class Templator
{
    private string $templateDir;
    private string $cacheDir;
    /** @var array<string, mixed> */
    private $sections     = [];
    public string $suffix = '';
    public int $max_depth = 5;
    private Manifestor $manifest;
    /**
     * Template dependency.
     *
     * @var string[]
     */
    private $dependency = [];

    public function __construct(string $templateDir, string $cacheDir, string $manifest = '/manifest.json')
    {
        $this->templateDir = $templateDir;
        $this->cacheDir    = $cacheDir;
        $this->manifest    = new Manifestor($templateDir, $cacheDir, $manifest);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $templateName, array $data, bool $cache = true): string
    {
        $templateName .= $this->suffix;
        $templatePath  = $this->templateDir . '/' . $templateName;

        if (!file_exists($templatePath)) {
            throw new ViewFileNotFound($templatePath);
        }

        $alias     = md5($templateName) . '.php';
        $cachePath = $this->cacheDir . '/' . $alias;

        if ($cache && file_exists($cachePath) && $this->manifest->isDependencyUptodate($alias)) {
            return $this->getView($cachePath, $data);
        }
        $this->addDependency($templateName);

        $template = file_get_contents($templatePath);
        $template = $this->templates($template);

        file_put_contents($cachePath, $template);
        $this->manifest->replaceDependency($alias, $this->dependency);

        return $this->getView($cachePath, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getView(string $tempalte_path, array $data): string
    {
        $level = ob_get_level();

        ob_start();

        try {
            extract($data);
            include $tempalte_path;
        } catch (\Throwable $th) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $th;
        }

        $out = ob_get_clean();

        return $out === false ? '' : ltrim($out);
    }

    private function templates(string $template): string
    {
        $template = $this->templateSlot($template);
        $template = $this->templateInclude($template, $this->max_depth);
        $template = $this->templatePhp($template);
        $template = $this->templateName($template);
        $template = $this->templateIf($template);
        $template = $this->templateEach($template);
        $template = $this->templateComment($template);

        return $template;
    }

    private function templateSlot(string $template): string
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
            function ($matches) use ($matches_layout) {
                if (array_key_exists($matches[1], $this->sections)) {
                    return $this->sections[$matches[1]];
                }

                throw new \Exception("Slot with extends '{$matches_layout[1]}' required '{$matches[1]}'");
            },
            $layout
        );

        $this->addDependency($matches_layout[1]);

        return $template;
    }

    private function templateInclude(string $template, int $maks_dept): string
    {
        return preg_replace_callback(
            '/{%\s*include\s*\(\s*[\'"]([^\'"]+)[\'"]\s*\)\s*%}/',
            function ($matches) use ($maks_dept) {
                $templatePath = $this->templateDir . '/' . $matches[1];

                if (!file_exists($templatePath)) {
                    throw new \Exception('Template file not found: ' . $matches[1]);
                }

                $includedTemplate = file_get_contents($templatePath);
                $this->addDependency($matches[1]);
                if ($maks_dept === 0) {
                    return $includedTemplate;
                }

                return trim($this->templateInclude($includedTemplate, --$maks_dept));
            },
            $template
        );
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

    private function templateEach(string $template): string
    {
        return preg_replace('/{%\s*foreach\s+([^%]+)\s+as\s+([^%]+)\s*%}(.*?){%\s*endforeach\s*%}/s', '<?php foreach ($$1 as $$2): ?>$3<?php endforeach; ?>', $template);
    }

    private function templateComment(string $template): string
    {
        return preg_replace('/{#\s*(.*?)\s*#}/', '<?php // $1 ?>', $template);
    }

    private function addDependency(string $templatename): void
    {
        if (false === in_array($templatename, $this->dependency)) {
            $this->dependency[] = $templatename;
        }
    }
}
