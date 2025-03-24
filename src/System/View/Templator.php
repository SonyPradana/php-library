<?php

declare(strict_types=1);

namespace System\View;

use System\View\Templator\BooleanTemplator;
use System\View\Templator\BreakTemplator;
use System\View\Templator\CommentTemplator;
use System\View\Templator\ComponentTemplator;
use System\View\Templator\ContinueTemplator;
use System\View\Templator\DirectiveTemplator;
use System\View\Templator\EachTemplator;
use System\View\Templator\IfTemplator;
use System\View\Templator\IncludeTemplator;
use System\View\Templator\JsonTemplator;
use System\View\Templator\NameTemplator;
use System\View\Templator\PHPTemplator;
use System\View\Templator\SectionTemplator;
use System\View\Templator\SetTemplator;
use System\View\Templator\UseTemplator;

class Templator
{
    protected TemplatorFinder $finder;
    private string $cacheDir;
    public string $suffix               = '';
    public int $max_depth               = 5;
    private string $component_namespace = '';
    /** @var array<string, array<string, int>> */
    private array $dependency = [];

    /**
     * Create new intance.
     *
     * @param TemplatorFinder|string $finder If String will genarte TemplatorFinder with default extension
     */
    public function __construct($finder, string $cacheDir)
    {
        // Backwards compatibility with templator finder.
        $this->finder    = is_string($finder) ? new TemplatorFinder([$finder]) : $finder;
        $this->cacheDir  = $cacheDir;
    }

    /**
     * Set Finder.
     */
    public function setFinder(TemplatorFinder $finder): self
    {
        $this->finder = $finder;

        return $this;
    }

    /**
     * Set Component Namespace.
     */
    public function setComponentNamespace(string $namespace): self
    {
        $this->component_namespace = $namespace;

        return $this;
    }

    public function addDependency(string $perent, string $child, int $depend_deep = 1): self
    {
        $this->dependency[$perent][$child] = $depend_deep;

        return $this;
    }

    /**
     * Prepend Dependency.
     *
     * @param array<string, int> $childs
     */
    public function prependDependency(string $perent, array $childs): self
    {
        foreach ($childs as $child => $depth) {
            if ($has_depeth = isset($this->dependency[$perent][$child]) && $depth > $this->dependency[$perent][$child]) {
                $this->addDependency($perent, $child, $depth);
            }

            if (false === $has_depeth) {
                $this->addDependency($perent, $child, $depth);
            }
        }

        return $this;
    }

    /**
     * @return array<string, int>
     */
    public function getDependency(string $perent): array
    {
        return $this->dependency[$perent] ?? [];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function render(string $templateName, array $data, bool $cache = true): string
    {
        $templateName .= $this->suffix;
        $templatePath  = $this->finder->find($templateName);

        $cachePath = $this->cacheDir . '/' . md5($templateName) . '.php';

        if ($cache && file_exists($cachePath) && filemtime($cachePath) >= filemtime($templatePath)) {
            return $this->getView($cachePath, $data);
        }

        $template = file_get_contents($templatePath);
        $template = $this->templates($template, $templatePath);

        file_put_contents($cachePath, $template);

        return $this->getView($cachePath, $data);
    }

    /**
     * Compile templator file to php file.
     */
    public function compile(string $template_name): string
    {
        $template_name .= $this->suffix;
        $template_dir  = $this->finder->find($template_name);

        $cachePath = $this->cacheDir . '/' . md5($template_name) . '.php';

        $template = file_get_contents($template_dir);
        $template = $this->templates($template, $template_dir);

        file_put_contents($cachePath, $template);

        return $template;
    }

    /**
     * Check view file exist.
     */
    public function viewExist(string $templateName): bool
    {
        $templateName .= $this->suffix;

        return $this->finder->exists($templateName);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function getView(string $tempalte_path, array $data): string
    {
        $level = ob_get_level();

        ob_start();

        try {
            (static function ($__, $__file_name__) {
                extract($__);
                include $__file_name__;
            })($data, $tempalte_path);
        } catch (\Throwable $th) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $th;
        }

        $out = ob_get_clean();

        return $out === false ? '' : ltrim($out);
    }

    /**
     * Transform templator to php template.
     */
    public function templates(string $template, string $view_location = ''): string
    {
        return array_reduce([
            SetTemplator::class,
            SectionTemplator::class,
            IncludeTemplator::class,
            PHPTemplator::class,
            DirectiveTemplator::class,
            ComponentTemplator::class,
            NameTemplator::class,
            IfTemplator::class,
            EachTemplator::class,
            CommentTemplator::class,
            ContinueTemplator::class,
            BreakTemplator::class,
            UseTemplator::class,
            JsonTemplator::class,
            BooleanTemplator::class,
        ], function (string $template, string $templator) use ($view_location): string {
            $templator = new $templator($this->finder, $this->cacheDir);
            if ($templator instanceof IncludeTemplator) {
                $templator->maksDept($this->max_depth);
            }

            if ($templator instanceof ComponentTemplator) {
                $templator->setNamespace($this->component_namespace);
            }

            $parse = $templator->parse($template);

            // Get dependecy view file (perent) after parse template.
            if ($templator instanceof DependencyTemplatorInterface) {
                $this->prependDependency($view_location, $templator->dependentOn());
            }

            return $parse;
        }, $template);
    }
}
