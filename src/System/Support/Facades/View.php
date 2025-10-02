<?php

declare(strict_types=1);

namespace System\Support\Facades;

/**
 * @method static \System\View\Templator setFinder(\System\View\TemplatorFinder $finder)
 * @method static \System\View\Templator setComponentNamespace(string $namespace)
 * @method static \System\View\Templator addDependency(string $perent, string $child, int $depend_deep = 1)
 * @method static \System\View\Templator prependDependency(array<string, int> $childs, string $perent)
 * @method static array<string, int>     getDependency(string $perent)
 * @method static string                 render(array<string, mixed> $data, string $templateName, bool $cache = true)
 * @method static string                 compile(string $template_name)
 * @method static bool                   viewExist(string $templateName)
 * @method static string                 templates(string $template, string $view_location = '')
 *
 * @see System\View\Templator
 */
final class View extends Facade
{
    protected static function getAccessor()
    {
        return 'view.instance';
    }
}
